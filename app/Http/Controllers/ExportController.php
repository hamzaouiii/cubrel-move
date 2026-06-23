<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Modules\LineItem;
use App\Support\PdfValueRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use function in_array;
use function is_array;
use function is_string;

class ExportController extends Controller
{
    public function export(Request $request, string $module, string $recordId)
    {
        $format = $request->query('format', 'json');
        abort_unless(in_array($format, ['json', 'csv']), 400, 'Invalid export format.');

        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $record = $this->resolveRecord($moduleModel, $recordId);
        $fields = $moduleModel->allFields();
        $renderer = app(PdfValueRenderer::class);

        $data = $this->buildExportRow($fields, $record, $format, $renderer);

        $lineItems = null;
        if ($moduleModel->has_line_items) {
            $lineItems = $this->resolveLineItems($recordId, $module, $renderer);
        }

        $filename = Str::slug($module.'-'.($record['number'] ?? $record['name'] ?? $recordId));

        if ($format === 'json') {
            if ($lineItems !== null) {
                $data['line_items'] = $lineItems['rows'];
            }

            return $this->respondJson($data, $filename);
        }

        return $this->respondCsv($data, $filename, $lineItems);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // exportMany
    //
    // Same three selection modes as RecordController::destroyMany/updateMany:
    //
    //   1. Explicit list  — allMatchingSelected=false, selectedIds=[1,2,3]
    //   2. All matching   — allMatchingSelected=true,  excludedIds=[]
    //   3. All except     — allMatchingSelected=true,  excludedIds=[4,5]
    //
    // Produces one merged file (CSV: one row per record, JSON: array of
    // record objects). Line items are not included — they don't fit a flat
    // merged row when records have a different number of them.
    // ─────────────────────────────────────────────────────────────────────────
    public function exportMany(Request $request, string $module)
    {
        $format = $request->input('format', 'json');
        abort_unless(in_array($format, ['json', 'csv']), 400, 'Invalid export format.');

        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $modelClass = $moduleModel->model_class;
        abort_if(! $modelClass || ! class_exists($modelClass), 500, 'No model class for module.');

        $handlerClass = $moduleModel->handler_class
            ?? 'App\\Handlers\\Modules\\'.Str::studly($moduleModel->slug).'ModuleHandler';

        $selectedIds = $this->cleanIds($request->input('selectedIds', []));
        $excludedIds = $this->cleanIds($request->input('excludedIds', []));
        $allMatchingSelected = (bool) $request->input('allMatchingSelected', false);
        $filters = (array) $request->input('filters', []);

        if (! $allMatchingSelected && count($selectedIds) === 0) {
            return back()->with('error', 'No records selected.');
        }

        $baseQuery = $modelClass::query();

        if ($allMatchingSelected) {
            if (class_exists($handlerClass)) {
                $handler = app($handlerClass);
                $searchable = $handler->getSearchableColumns($moduleModel);
            } else {
                $searchable = ['name', 'email', 'description'];
            }

            $search = trim((string) Arr::get($filters, 'search', ''));
            if ($search !== '') {
                $existing = array_filter($searchable, fn ($col) => Schema::hasColumn((new $modelClass)->getTable(), $col));
                if (! empty($existing)) {
                    $baseQuery->where(function ($q) use ($existing, $search) {
                        foreach ($existing as $col) {
                            $q->orWhere($col, 'like', "%{$search}%");
                        }
                    });
                }
            }

            if (! empty($excludedIds)) {
                $baseQuery->whereNotIn('id', $excludedIds);
            }
        } else {
            $baseQuery->whereIn('id', $selectedIds);
        }

        $ids = $baseQuery->pluck('id')->all();
        abort_if(empty($ids), 404, 'No matching records to export.');

        $fields = $moduleModel->allFields();
        $renderer = app(PdfValueRenderer::class);

        $rows = [];
        foreach ($ids as $id) {
            $record = $this->resolveRecord($moduleModel, (string) $id);
            $rows[] = $this->buildExportRow($fields, $record, $format, $renderer);
        }

        $filename = Str::slug($module.'-export-'.now()->format('Y-m-d'));

        return $format === 'json'
            ? $this->respondJson($rows, $filename)
            : $this->respondCsvMany($rows, $filename);
    }

    private function buildExportRow(Collection $fields, array $record, string $format, PdfValueRenderer $renderer): array
    {
        $data = [];

        foreach ($fields as $field) {
            if ($field->hidden) {
                continue;
            }

            $label = $this->translateLabel($field->label);
            $fieldName = $field->name;
            $value = $record[$fieldName] ?? null;

            // Related record: prefer the __label companion resolved by the handler
            if ($field->type === 'record') {
                $labelValue = $record[$fieldName.'__label'] ?? null;
                $data[$label] = $labelValue !== null ? (string) $labelValue : '';
                continue;
            }

            if ($value === null || $value === '') {
                $data[$label] = '';
                continue;
            }

            // Address: JSON gets a structured object, CSV gets a single formatted line
            if ($field->type === 'address') {
                $data[$label] = $format === 'json'
                    ? $this->parseAddress($value)
                    : $this->formatAddressLine($value);
                continue;
            }

            $dropdownValues = $field->dropdown_list?->values ?? null;
            $rendered = $renderer->render($field->type, $value, $dropdownValues);

            // PdfValueRenderer returns '—' for empty/null — use empty string for exports
            $data[$label] = $rendered === '—' ? '' : $rendered;
        }

        return $data;
    }

    /**
     * Strip null / empty-string values from an ID array coming from the frontend.
     */
    private function cleanIds(mixed $input): array
    {
        return array_values(
            array_filter((array) $input, fn ($id) => $id !== null && $id !== '')
        );
    }

    private function resolveRecord(Module $moduleModel, string $recordId): array
    {
        $handlerClass = $moduleModel->handler_class
            ?? 'App\\Handlers\\Modules\\'.Str::studly($moduleModel->slug).'ModuleHandler';

        if (class_exists($handlerClass)) {
            $handler = app($handlerClass);

            if (method_exists($handler, 'getRecordData')) {
                $result = $handler->getRecordData($moduleModel->slug, $recordId, $moduleModel);

                return $result['record'] ?? [];
            }
        }

        $modelClass = $moduleModel->model_class;
        abort_if(! $modelClass || ! class_exists($modelClass), 500, 'No model class for module.');

        $record = $modelClass::findOrFail($recordId);
        $customFields = $record->custom_fields ?? [];

        return array_merge($record->toArray(), $customFields);
    }

    private function resolveLineItems(string $recordId, string $module, PdfValueRenderer $renderer): array
    {
        $lineItemsModule = Module::query()->where('slug', 'line_items')->first();
        $lineItemFields = $lineItemsModule ? $lineItemsModule->allFields() : collect();

        $items = LineItem::query()
            ->where('parent_type', $module)
            ->where('parent_id', $recordId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $fieldMap = [];
        foreach ($lineItemFields as $field) {
            if ($field->hidden) {
                continue;
            }
            $fieldMap[] = [
                'field' => $field,
                'label' => $this->translateLabel($field->label),
            ];
        }

        $rows = [];
        foreach ($items as $item) {
            $row = [];
            foreach ($fieldMap as $entry) {
                $field = $entry['field'];
                $label = $entry['label'];
                $value = $item[$field->name] ?? null;

                if ($value === null || $value === '') {
                    $row[$label] = '';
                    continue;
                }

                $dropdownValues = $field->dropdown_list?->values ?? null;
                $rendered = $renderer->render($field->type, $value, $dropdownValues);
                $row[$label] = $rendered === '—' ? '' : $rendered;
            }
            $rows[] = $row;
        }

        return [
            'headers' => array_column($fieldMap, 'label'),
            'rows'    => $rows,
        ];
    }

    private function translateLabel(string $key): string
    {
        $translated = __($key);

        return $translated !== $key ? $translated : $key;
    }

    private function parseAddress(mixed $value): ?array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : null;
        }

        if (! is_array($value)) {
            return null;
        }

        return array_filter([
            'street'      => ($value['street']      ?? '') ?: null,
            'postal_code' => ($value['postal_code'] ?? '') ?: null,
            'city'        => ($value['city']        ?? '') ?: null,
            'state'       => ($value['state']       ?? '') ?: null,
            'country'     => ($value['country']     ?? '') ?: null,
        ]) ?: null;
    }

    private function formatAddressLine(mixed $value): string
    {
        $parsed = $this->parseAddress($value);
        if (! $parsed) {
            return '';
        }

        $cityLine = trim(($parsed['postal_code'] ?? '').' '.($parsed['city'] ?? ''));
        $regionLine = implode(', ', array_filter([$parsed['state'] ?? null, $parsed['country'] ?? null]));

        return implode(', ', array_filter([
            $parsed['street'] ?? null,
            $cityLine ?: null,
            $regionLine ?: null,
        ]));
    }

    private function respondJson(array $data, string $filename): \Illuminate\Http\Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.json"',
        ]);
    }

    private function respondCsv(array $data, string $filename, ?array $lineItems): \Illuminate\Http\Response
    {
        $buffer = fopen('php://temp', 'r+');

        // Main record: header row + data row
        fputcsv($buffer, array_keys($data));
        fputcsv($buffer, array_map(
            fn ($v) => is_array($v) ? json_encode($v) : (string) $v,
            array_values($data)
        ));

        // Line items section
        if ($lineItems !== null && ! empty($lineItems['rows'])) {
            fputcsv($buffer, []);
            fputcsv($buffer, [__('globals.export.csv_line_items_section')]);
            fputcsv($buffer, $lineItems['headers']);
            foreach ($lineItems['rows'] as $row) {
                fputcsv($buffer, array_map(
                    fn ($v) => is_array($v) ? json_encode($v) : (string) $v,
                    array_values($row)
                ));
            }
        }

        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }

    private function respondCsvMany(array $rows, string $filename): \Illuminate\Http\Response
    {
        $buffer = fopen('php://temp', 'r+');

        fputcsv($buffer, array_keys($rows[0] ?? []));
        foreach ($rows as $row) {
            fputcsv($buffer, array_map(
                fn ($v) => is_array($v) ? json_encode($v) : (string) $v,
                array_values($row)
            ));
        }

        rewind($buffer);
        $content = stream_get_contents($buffer);
        fclose($buffer);

        return response($content, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }
}
