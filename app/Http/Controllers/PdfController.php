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
use Dompdf\FontMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PdfController extends Controller
{
    /**
     * Summary of generate
     *
     * @return \Illuminate\Http\Response
     *There is a big concern here about performance, if several users generate pdf at the same time, the app will collapse. We are limiting users then to 20 per organisation as a result
     * V2 should move pdf generation to a queue worker and preferably move away from Dompdf to Gotenberg
     * https://gotenberg.dev/
     */
    public function generate(Request $request, string $module, string $recordId)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        // Allow callers to request a specific template via ?template=<id>;
        // fall back to the module's default template when no ID is provided.
        $templateId = $request->query('template');
        if ($templateId) {
            $template = PdfTemplate::where('id', $templateId)
                ->where('module_slug', $module)
                ->firstOrFail();
        } else {
            $template = PdfTemplate::defaultFor($module);
            abort_if($template === null, 404, 'No PDF template configured for this module.');
        }

        $record = $this->resolveRecord($moduleModel, $recordId);
        $fields = $moduleModel->allFields();
        $company = $this->companySettings();

        $lineItemsModule = Module::query()->where('slug', 'line_items')->first();
        $lineItemFields = $lineItemsModule ? $lineItemsModule->allFields() : collect();

        // Sections come from the template's own definition, or fall back to the
        // linked Layout's definition for templates created through the old flow.
        $sections = [];
        $lineItems = [];
        if ($template->definition) {
            $sections = $template->definition['sections'] ?? [];
        } elseif ($template->layout_id) {
            $layout = Layout::find($template->layout_id);
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

        // Collect every relationship name referenced by field items across all sections,
        // including field items placed inside header/footer row slots.
        $relNames = [];
        foreach ($sections as $section) {
            foreach ($section['items'] ?? [] as $item) {
                $r = $item['relationship'] ?? null;
                if ($r) {
                    $relNames[$r] = true;
                }
            }
            foreach ($section['rows'] ?? [] as $row) {
                foreach (['left', 'right'] as $side) {
                    $r = ($row[$side] ?? [])['relationship'] ?? null;
                    if ($r) {
                        $relNames[$r] = true;
                    }
                }
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
        // mergeWithDefaults=true preserves barryvdh config (chroot=base_path, fontDir=storage/fonts)
        // so registerFont can read from resources/fonts and write to storage/fonts.
        $pdf = Pdf::setOptions(['isRemoteEnabled' => true], true);
        $this->registerFonts($pdf->getFontMetrics());
        $pdf->loadView($template->blade_view, [
            'record' => $record,
            'module' => $module,
            'moduleLabel' => "modules.{$moduleModel->slug}.single_label",
            'fields' => $fields,
            'company' => $company,
            'renderer' => app(PdfValueRenderer::class),
            'sections' => $sections,
            'lineItems' => $lineItems,
            'relationshipData' => $relationshipData,
            'currency' => Settings::get('default_currency', ''),
            'lineItemFields' => $lineItemFields,
        ])
            ->setPaper('a4', 'portrait');

        // Use the record's human-readable number when available; fall back to the raw ID.
        $filename = Str::slug($moduleModel->slug).'-'.($record['number'] ?? $recordId).'.pdf';

        // stream() displays the PDF inline in the browser instead of forcing a download.
        return $pdf->stream($filename);
    }

    /**
     * Register Fira Sans and Heebo TTF files with DomPDF before rendering so they
     * are available by name in the CSS without needing @font-face data URIs (which
     * php-font-lib cannot reliably parse from in-memory streams).
     */
    private function registerFonts(FontMetrics $fontMetrics): void
    {
        $dir = resource_path('fonts');
        $fonts = [
            [['family' => 'Fira Sans', 'weight' => '300',    'style' => 'normal'], 'fira-sans-v18-latin-300.ttf'],
            [['family' => 'Fira Sans', 'weight' => 'normal',  'style' => 'normal'], 'fira-sans-v18-latin-regular.ttf'],
            [['family' => 'Fira Sans', 'weight' => '500',    'style' => 'normal'], 'fira-sans-v18-latin-500.ttf'],
            [['family' => 'Fira Sans', 'weight' => '600',    'style' => 'normal'], 'fira-sans-v18-latin-600.ttf'],
            [['family' => 'Fira Sans', 'weight' => 'bold',   'style' => 'normal'], 'fira-sans-v18-latin-700.ttf'],
            [['family' => 'Fira Sans', 'weight' => '900',    'style' => 'normal'], 'fira-sans-v18-latin-900.ttf'],
            [['family' => 'Heebo',     'weight' => '300',    'style' => 'normal'], 'heebo-v28-latin-300.ttf'],
            [['family' => 'Heebo',     'weight' => 'normal',  'style' => 'normal'], 'heebo-v28-latin-regular.ttf'],
            [['family' => 'Heebo',     'weight' => '500',    'style' => 'normal'], 'heebo-v28-latin-500.ttf'],
            [['family' => 'Heebo',     'weight' => '600',    'style' => 'normal'], 'heebo-v28-latin-600.ttf'],
            [['family' => 'Heebo',     'weight' => 'bold',   'style' => 'normal'], 'heebo-v28-latin-700.ttf'],
            [['family' => 'Heebo',     'weight' => '900',    'style' => 'normal'], 'heebo-v28-latin-900.ttf'],
        ];

        foreach ($fonts as [$style, $file]) {
            $path = $dir.DIRECTORY_SEPARATOR.$file;
            if (file_exists($path)) {
                $fontMetrics->registerFont($style, $path);
            }
        }
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

        abort_if(! $modelClass || ! class_exists($modelClass), 500, 'No model class for module.');

        $record = $modelClass::findOrFail($recordId);
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

        if (! empty($logoUrl)) {
            $logoUrl = $this->logoAsDataUri($logoUrl);
        }

        return [
            'name' => Settings::get('company_name', config('app.name')),
            'address' => Settings::get('company_address', ''),
            'phone' => Settings::get('company_phone', ''),
            'email' => Settings::get('company_email', ''),
            'website' => Settings::get('company_website', ''),
            'logo_url' => $logoUrl,
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
        return cache()->remember('pdf_logo_'.md5($url), now()->addHours(6), function () use ($url) {
            // Try reading from disk first (avoids HTTP round-trip for local URLs)
            $appUrl = rtrim(config('app.url'), '/');
            if (str_starts_with($url, $appUrl)) {
                $path = public_path(parse_url($url, PHP_URL_PATH));
                if (file_exists($path)) {
                    $data = file_get_contents($path);
                    $mime = mime_content_type($path) ?: 'image/png';

                    return 'data:'.$mime.';base64,'.base64_encode($data);
                }
            }

            // Remote URL — short timeout, don't block for long
            try {
                $context = stream_context_create(['http' => ['timeout' => 2]]);
                $data = @file_get_contents($url, false, $context);

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

                return 'data:'.$mime.';base64,'.base64_encode($data);
            } catch (\Throwable) {
                return $url;
            }
        });
    }
}
