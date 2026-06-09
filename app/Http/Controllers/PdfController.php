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
    /**
     * Generate and stream a PDF for a single module record.
     *
     * Flow:
     *   1. Resolve the active Module by slug — 404 if unknown or inactive.
     *   2. Find the default PdfTemplate for this module — 404 if none configured.
     *   3. Load the record (standard + custom fields merged into a flat array).
     *   4. Load the layout sections and line items that define the PDF body.
     *   5. Collect every relationship referenced in those sections and eager-load
     *      the related records so the Blade view can render them without N+1 queries.
     *   6. Render the Blade view via DomPDF and stream the result inline to the browser.
     */
    // called from ex: /invoices/019eab70-5a3b-72ee-82eb-a1f58626077d
    public function generate(string $module, string $recordId)
    {
        // Resolve the module by its URL slug; reject unknown or disabled modules early.
        $moduleModel = Module::query()
            ->where('slug', $module) // invoices
            ->where('is_active', true)
            ->firstOrFail();

        // Every module that supports PDF export must have a default template configured - for now
        // the future of this feature is having multiple templates per module, plus global templates (perhaps) 
        $template = PdfTemplate::defaultFor($module);

        abort_if($template === null, 404, 'No PDF template configured for this module.');

        $record  = $this->resolveRecord($moduleModel, $recordId);
        $fields  = $moduleModel->allFields();
        $company = $this->companySettings();

        // Sections come from the linked Layout's definition JSON.
        // If the template has no layout, $sections stays empty and the view
        // renders without structured sections.
        $sections  = [];
        $lineItems = [];
        if ($template->layout_id) {
            $layout   = Layout::find($template->layout_id);
            $sections = $layout?->definition['sections'] ?? [];
        }

        // Line items are ordered by sort_order so they appear in the correct sequence
        // (e.g. invoice lines, quote items).
        $lineItems = LineItem::query()
            ->where('parent_type', $module)
            ->where('parent_id', $recordId)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        // Collect every relationship name referenced by field items across all sections.
        $relNames = [];
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $r = $item['relationship'] ?? null;
                if ($r) $relNames[$r] = true;
            }
        }

        // Pre-load all related records so the Blade view can render relationship
        // sections without additional queries. Failures are silenced to prevent a
        // broken relationship from blocking the entire PDF.
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

        // isRemoteEnabled allows DomPDF to fetch external assets, though the logo
        // is already embedded as a data-URI to avoid HTTP round-trips at render time.
        $pdf = Pdf::setOptions(['isRemoteEnabled' => true])
            ->loadView($template->blade_view, [
                'record'           => $record,
                'module'           => $module,
                'moduleLabel'      => "modules.{$moduleModel->slug}.single_label",
                'fields'           => $fields,
                'company'          => $company,
                'renderer'         => app(PdfValueRenderer::class),
                'sections'         => $sections,
                'lineItems'        => $lineItems,
                'relationshipData' => $relationshipData,
            ])
            ->setPaper('a4', 'portrait');

        // Use the record's human-readable number when available; fall back to the raw ID.
        $filename = Str::slug($moduleModel->slug) . '-' . ($record['number'] ?? $recordId) . '.pdf';

        // stream() displays the PDF inline in the browser instead of forcing a download.
        return $pdf->stream($filename);
    }

    /**
     * Load the record and merge custom_fields into a single flat array.
     *
     * The custom_fields column stores a JSON object of ad-hoc field values.
     * Merging them at this level means the Blade view and PdfValueRenderer can
     * access all field values through a uniform $record['key'] pattern.
     */
    private function resolveRecord(Module $moduleModel, string $recordId): array
    {
        $modelClass = $moduleModel->model_class;

        abort_if(!$modelClass || !class_exists($modelClass), 500, 'No model class for module.');

        $record       = $modelClass::findOrFail($recordId);
        $customFields = $record->custom_fields ?? [];

        return array_merge($record->toArray(), $customFields);
    }

    /**
     * Read company branding from the Settings store and convert the logo to a
     * Base64 data-URI so DomPDF can embed it without making HTTP calls at render time.
     */
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

    /**
     * Convert a logo URL to a Base64-encoded data-URI, cached for 6 hours.
     *
     * Local URLs are read directly from disk to avoid an HTTP round-trip.
     * Remote URLs are fetched with a 2-second timeout; if the fetch fails the
     * original URL is returned so DomPDF can attempt its own retrieval.
     */
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
