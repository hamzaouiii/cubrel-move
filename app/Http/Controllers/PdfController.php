<?php

namespace App\Http\Controllers;

use App\Models\Layout;
use App\Models\Module;
use App\Models\Modules\LineItem;
use App\Models\PdfTemplate;
use App\Services\Relationships\RelationshipService;
use App\Support\PdfValueRenderer;
use App\Support\Settings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    public function generate(string $module, string $recordId)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $template = PdfTemplate::defaultFor($module);

        abort_if($template === null, 404, 'No PDF template configured for this module.');

        $record  = $this->resolveRecord($moduleModel, $recordId);
        $fields  = $moduleModel->allFields();
        $company = $this->companySettings();

        $sections  = [];
        $lineItems = [];
        if ($template->layout_id) {
            $layout   = Layout::find($template->layout_id);
            $sections = $layout?->definition['sections'] ?? [];
        }
        $lineItems = LineItem::query()
            ->where('parent_type', $module)
            ->where('parent_id', $recordId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        // Collect every relationship name referenced by field items (new model)
        // or legacy relationship sections (backward compat).
        $relNames = [];
        foreach ($sections as $section) {
            if (($section['type'] ?? '') === 'relationship') {
                $r = $section['relationship'] ?? null;
                if ($r) $relNames[$r] = true;
            }
            foreach ($section['items'] ?? [] as $item) {
                $r = $item['relationship'] ?? null;
                if ($r) $relNames[$r] = true;
            }
        }

        $relationshipData = [];
        foreach (array_keys($relNames) as $relName) {
            try {
                $relationshipData[$relName] = RelationshipService::getRelatedRecords($relName, $module, $recordId)
                    ->map(fn ($r) => $r->toArray())
                    ->toArray();
            } catch (\Throwable) {
                $relationshipData[$relName] = [];
            }
        }

        $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView($template->blade_view, [
                'record'           => $record,
                'module'           => $module,
                'moduleLabel'      => $moduleModel->name,
                'fields'           => $fields,
                'company'          => $company,
                'renderer'         => app(PdfValueRenderer::class),
                'sections'         => $sections,
                'lineItems'        => $lineItems,
                'relationshipData' => $relationshipData,
            ])
            ->setPaper('a4', 'portrait');

        $filename = Str::slug($moduleModel->name) . '-' . ($record['number'] ?? $recordId) . '.pdf';

        return $pdf->stream($filename);
    }

    private function resolveRecord(Module $moduleModel, string $recordId): array
    {
        $modelClass = $moduleModel->model_class;

        abort_if(!$modelClass || !class_exists($modelClass), 500, 'No model class for module.');

        $record       = $modelClass::findOrFail($recordId);
        $customFields = $record->custom_fields ?? [];

        return array_merge($record->toArray(), $customFields);
    }

    private function companySettings(): array
    {
        $logoUrl = Settings::get('company_logo_url', '');

        if (!empty($logoUrl)) {
            $logoUrl = $this->logoAsDataUri($logoUrl);
        }

        return [
            'name'      => Settings::get('company_name', config('app.name')),
            'address'   => Settings::get('company_address', ''),
            'phone'     => Settings::get('company_phone', ''),
            'email'     => Settings::get('company_email', ''),
            'website'   => Settings::get('company_website', ''),
            'logo_url'  => $logoUrl,
        ];
    }

    private function logoAsDataUri(string $url): string
    {
        return cache()->remember('pdf_logo_' . md5($url), now()->addHours(6), function () use ($url) {
            // Try reading from disk first (avoids HTTP round-trip for local URLs)
            $appUrl = rtrim(config('app.url'), '/');
            if (str_starts_with($url, $appUrl)) {
                $path = public_path(parse_url($url, PHP_URL_PATH));
                if (file_exists($path)) {
                    $data = file_get_contents($path);
                    $mime = mime_content_type($path) ?: 'image/png';
                    return 'data:' . $mime . ';base64,' . base64_encode($data);
                }
            }

            // Remote URL — short timeout, don't block for long
            try {
                $context = stream_context_create(['http' => ['timeout' => 2]]);
                $data    = @file_get_contents($url, false, $context);

                if ($data === false) {
                    return $url;
                }

                $mime = 'image/png';
                foreach ((array) ($http_response_header ?? []) as $header) {
                    if (stripos($header, 'content-type:') === 0) {
                        $mime = trim(explode(':', $header, 2)[1]);
                        break;
                    }
                }

                return 'data:' . $mime . ';base64,' . base64_encode($data);
            } catch (\Throwable) {
                return $url;
            }
        });
    }
}
