<?php

namespace App\Http\Controllers;

use App\Contracts\ModuleHandler;
use App\Exceptions\ModuleHandlerNotFoundException;
use App\Models\Field;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Models\PdfTemplate;

class RecordController extends Controller
{
    public function __invoke(string $module, string $recordId)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];
        $handler_class = $moduleModel->handler_class ?? "App\Handlers\Modules\\".ucwords($module).'ModuleHandler';

        if (empty($handler_class)) {
            throw new ModuleHandlerNotFoundException(
                "Handler class [{$handler_class}] not found for module [{$module}]. Please check if the file exists or re-deploy."
            );
        }

        if (! class_exists($handler_class)) {
            $props = [];
        } else {
            $handler = app($handler_class);

            if ($handler instanceof ModuleHandler || method_exists($handler, 'getRecordData')) {
                $props = $handler->getRecordData($module, $recordId, $moduleModel, request()->all());
            } else {
                $props = ['recordId' => $recordId];
            }
        }

        // current module record layout - overview
        $recordLayout = $moduleModel->recordLayout();
        
        // current module record layout - related panels
        $relatedLayout = $moduleModel->relatedLayout();
        
        // current module's fields definitions
        $fields = $moduleModel->allFields();

        // Line items are only relevant for modules that actually have them enabled —
        // everything below is skipped entirely otherwise, instead of the previous
        // unconditional lookup that ran (and could fatal) on every single module.
        $lineItemFields = collect();
        $sourceFields = collect();
        $sourceModuleSlug = null;
        $lineItemsListColumns = [];
        $lineItemsSnapshotLayout = [];

        if ($moduleModel->has_line_items) {
            $line_itemsModel = Module::query()
                ->where('slug', 'line_items')
                ->firstOrFail();
            $lineItemFields = $line_itemsModel->allFields();

            // The module line items snapshot/search from is configurable per host
            // module (see Module::lineItemSourceModuleSlug()); it falls back to
            // 'products' for modules that predate this setting. Resolved leniently
            // (not firstOrFail) so a since-deactivated source module degrades to an
            // empty picker instead of a hard 500 on every record view.
            $sourceModuleSlug = $moduleModel->lineItemSourceModuleSlug();
            $sourceModel = Module::query()
                ->where('slug', $sourceModuleSlug)
                ->where('is_active', true)
                ->first();
            $sourceFields = $sourceModel?->allFields() ?? collect();

            // The line-items table's columns live inside the record layout's own
            // "has_line_items" placeholder section (configured via the Layouts editor).
            $lineItemsSection = collect($recordLayout['sections'] ?? [])
                ->first(fn ($section) => ($section['has_line_items'] ?? false) === true);
            $lineItemsListColumns = $lineItemsSection['layout'] ?? [];

            $lineItemsSnapshotLayout = $moduleModel->lineItemsSnapshotLayout();
        }

        $pdfTemplates = PdfTemplate::where('module_slug', $moduleModel->slug)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name', 'is_default']);

        return Inertia::render('Modules/Record', array_merge([
            'module' => $moduleModel,
            'title' => $moduleModel->name,
            'recordId' => $recordId,
            'overviewLayout' => $recordLayout,
            'relatedLayout' => $relatedLayout,
            'fields' => $fields,
            'lineItemFields' => $lineItemFields,
            'productFields' => $sourceFields,
            'lineItemSourceModule' => $sourceModuleSlug,
            'lineItemsListColumns' => $lineItemsListColumns,
            'lineItemsSnapshotLayout' => $lineItemsSnapshotLayout,
            'pdfTemplates' => $pdfTemplates,
        ], $props));

    }

    public function create(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];

        $handler_class = $moduleModel->handler_class
          ?? 'App\\Handlers\\Modules\\'.Str::studly($module).'ModuleHandler';

        if (class_exists($handler_class)) {
            $handler = app($handler_class);
        }

        $recordLayout = $moduleModel->recordLayout();
        $recorddropdownLists = $moduleModel->dropdownLists;
        $fields = $moduleModel->allFields();

        return Inertia::render('Modules/Create', array_merge([
            'module' => $moduleModel,
            'title' => $moduleModel->name,
            'recordLayout' => $recordLayout,
            'dropdownLists' => $recorddropdownLists,
            'fields' => $fields,
        ], $props));
    }

    public function store(Request $request, string $module)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass = $moduleModel->model_class;

        $record = $modelClass::create(
            $request->except('_token')
        );

        if ($request->wantsJson()) {
            return response()->json($record);
        }

        return redirect("/{$module}/{$record->id}")
            ->with('success', 'Record created successfully.');
    }

    public function update(Request $request, string $module, string $id)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass = $moduleModel->model_class;

        $record = $modelClass::findOrFail($id);
        $record->fill($request->except('_token', '_method', 'related', 'owner_id__label'))->save();

        if ($request->wantsJson()) {
            return response()->json($record);
        }

        return back()->with('success', 'Record updated successfully.');
    }

    public function destroy(string $module, int|string $record)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass = $moduleModel->model_class;

        $model = $modelClass::findOrFail($record);
        $model->delete();

        return redirect()
            ->route('modules.index', $module)
            ->with('success', __('modules.actions.delete_success'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // destroyMany
    //
    // Accepts three selection modes:
    //
    //   1. Explicit list  — allMatchingSelected=false, selectedIds=[1,2,3]
    //   2. All matching   — allMatchingSelected=true,  excludedIds=[]
    //   3. All except     — allMatchingSelected=true,  excludedIds=[4,5]
    // ─────────────────────────────────────────────────────────────────────────
    public function destroyMany(Request $request, string $module)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass = $moduleModel->model_class;

        $handlerClass = $moduleModel->handler_class
          ?? 'App\\Handlers\\Modules\\'.Str::studly($moduleModel->slug).'ModuleHandler';

        $selectedIds = $this->cleanIds($request->input('selectedIds', []));
        $excludedIds = $this->cleanIds($request->input('excludedIds', []));
        $allMatchingSelected = (bool) $request->input('allMatchingSelected', false);
        $filters = (array) $request->input('filters', []);

        if (! class_exists($modelClass)) {
            abort(500, "Model class not found for module {$moduleModel->slug}");
        }

        if (! $allMatchingSelected && count($selectedIds) === 0) {
            return back()->with('error', 'No records selected.');
        }

        $baseQuery = $modelClass::query();

        if ($allMatchingSelected) {
            // Apply search filter if present
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

            // Exclude explicitly de-selected records
            if (! empty($excludedIds)) {
                $baseQuery->whereNotIn('id', $excludedIds);
            }

            $count = 0;
            DB::transaction(function () use ($baseQuery, &$count) {
                $baseQuery->select('id')->chunkById(500, function ($chunk) use (&$count) {
                    $ids = $chunk->pluck('id')->all();
                    $count += count($ids);
                    if (! empty($ids)) {
                        (clone $chunk)->first()->newQuery()->whereIn('id', $ids)->delete();
                    }
                });
            });

            return back()->with('success', "{$count} records deleted.");
        }

        // Explicit list mode
        $deleted = $modelClass::whereIn('id', $selectedIds)->delete();

        return back()->with('success', "{$deleted} records deleted.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // updateMany
    //
    // Same three-mode selection as destroyMany.
    // ─────────────────────────────────────────────────────────────────────────
    public function updateMany(Request $request, string $module)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass = $moduleModel->model_class;
        $modelHandler = $moduleModel->handler_class;

        if (! class_exists($modelClass)) {
            throw new ModuleHandlerNotFoundException(
                "Model class not found for module {$moduleModel->slug}"
            );
        }

        if (class_exists($modelHandler)) {
            $handler = app($modelHandler);
        } else {
            throw new ModuleHandlerNotFoundException(
                "Handler class [{$modelHandler}] not found for module [{$module}]"
            );
        }

        $field_key = $request->field ?? null;
        $newValue = $request->value ?? null;

        $field = Field::query()->where('key', $field_key)->first();

        if (! $field) {
            return back()->with('error', 'No field specified for update.');
        }

        $field_name = $field->name;
        $selectedIds = $this->cleanIds($request->input('selectedIds', []));
        $excludedIds = $this->cleanIds($request->input('excludedIds', []));
        $allMatchingSelected = (bool) ($request->allMatchingSelected ?? false);
        $filters = (array) ($request->filters ?? []);

        if (! $allMatchingSelected && count($selectedIds) === 0) {
            return back()->with('error', 'No records selected.');
        }

        $baseQuery = $modelClass::query();

        if ($allMatchingSelected) {
            $search = trim((string) Arr::get($filters, 'search', ''));

            if ($search !== '') {
                $searchable = $handler->getSearchableColumns($moduleModel);
                if (! empty($searchable)) {
                    $baseQuery->where(function ($q) use ($searchable, $search) {
                        foreach ($searchable as $column) {
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    });
                }
            }

            // Exclude explicitly de-selected records
            if (! empty($excludedIds)) {
                $baseQuery->whereNotIn('id', $excludedIds);
            }

            $value = $field->is_custom ? $newValue : $this->castValueForColumn($modelClass, $field->name, $newValue);

            $count = 0;
            DB::transaction(function () use ($baseQuery, $field, $value, &$count, $modelClass) {
                $baseQuery->select('id')->chunkById(500, function ($chunk) use ($field, $value, &$count, $modelClass) {
                    $ids = $chunk->pluck('id')->all();
                    if (! empty($ids)) {
                        $column = $field->is_custom
                          ? "custom_fields->{$field->name}"
                          : $field->name;

                        $count += $modelClass::whereIn('id', $ids)->update([$column => $value]);
                    }
                });
            });

            return back()->with('success', "{$count} records updated.");
        }

        // Explicit list mode
        if ($field->is_custom) {
            $updatedCount = $modelClass::whereIn('id', $selectedIds)
                ->update(["custom_fields->{$field_name}" => $newValue]);
        } else {
            $updatedCount = $modelClass::whereIn('id', $selectedIds)
                ->update([$field_name => $this->castValueForColumn($modelClass, $field_name, $newValue)]);
        }

        return back()->with('success', "{$updatedCount} records updated.");
    }

    /**
     * Run a raw bulk-edit value through the model's attribute casts, so values like
     * ISO datetime strings are normalized the same way Eloquent would on save().
     */
    private function castValueForColumn(string $modelClass, string $column, mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        $model = new $modelClass;
        $model->setAttribute($column, $value);

        return $model->getAttributes()[$column] ?? $value;
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
}
