<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Module;
use App\Models\PdfTemplate;
use App\Services\Relationships\RelationshipService;
use App\Support\PdfValueRenderer;
use App\Support\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Controller responsibel for PDF layout editor which generates JSON Definitions and stores them in table PDF_templates
 */
class PdfTemplatesController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search');
        $perPage = $request->get('perPage', Settings::get('list_view_limit', 15));

        $modules = Module::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name', 'label', 'color', 'icon'])
            ->keyBy('slug');

        $query = PdfTemplate::orderBy('module_slug')->orderBy('name');

        if ($search) {
            $matchingSlugs = $modules->filter(
                fn ($m) => str_contains(strtolower($m->label ?? $m->name), strtolower($search))
            )->keys();

            $query->where(function ($q) use ($search, $matchingSlugs) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereIn('module_slug', $matchingSlugs);
            });
        }

        $paginator = $query->paginate($perPage);

        $last  = $paginator->lastPage();
        $pages = [];
        for ($p = 1; $p <= $last; $p++) {
            $pages[] = [
                'label'  => (string) $p,
                'page'   => $p,
                'url'    => $paginator->url($p),
                'active' => $p === $paginator->currentPage(),
            ];
        }

        return Inertia::render('Settings/PdfTemplates/Index', [
            'templates'   => $paginator->items(),
            'pdf_modules' => $modules,
            'meta'        => [
                'total'       => $paginator->total(),
                'perPage'     => $paginator->perPage(),
                'currentPage' => $paginator->currentPage(),
                'lastPage'    => $last,
                'from'        => $paginator->firstItem(),
                'to'          => $paginator->lastItem(),
                'links'       => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
                'pages' => $pages,
            ],
            'filters' => $request->only(['search', 'perPage']),
        ]);
    }

    public function create(Request $request)
    {
        $modules = Module::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name', 'label', 'color', 'icon', 'has_line_items']);

        $selectedModule = null;
        $fields = collect();
        $relationships = [];
        $lineItemFields = collect();

        if ($slug = $request->get('module')) {
            $selectedModule = Module::where('slug', $slug)->first();

            if ($selectedModule) {
                $fields = $selectedModule->allFields();
                $relationships = RelationshipService::getRelationshipForModule($slug);
                $lineItemsModule = Module::where('slug', 'line_items')->first();
                $lineItemFields = $lineItemsModule?->allFields() ?? collect();
            }
        }

        return Inertia::render('Settings/PdfTemplates/Create', [
            'selectedModule' => $selectedModule,
            'fields' => $fields,
            'relationships' => $relationships,
            'lineItemFields' => $lineItemFields,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'module_slug'        => 'required|string',
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:500',
            'is_default'         => 'boolean',
            'definition'         => 'required|array',
            'definition.sections' => 'required|array',
        ]);

        if (!empty($validated['is_default'])) {
            PdfTemplate::where('module_slug', $validated['module_slug'])
                ->update(['is_default' => false]);
        }

        PdfTemplate::create([
            'module_slug' => $validated['module_slug'],
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'blade_view'  => 'pdf.layout-driven',
            'definition'  => $validated['definition'],
            'is_default'  => $validated['is_default'] ?? false,
        ]);

        return redirect()
            ->route('settings.pdf-templates.index')
            ->with('success', 'PDF template created successfully.');
    }

    public function edit(PdfTemplate $pdfTemplate)
    {
        $moduleModel = Module::where('slug', $pdfTemplate->module_slug)->firstOrFail();
        $fields = $moduleModel->allFields();
        $relationships = RelationshipService::getRelationshipForModule($pdfTemplate->module_slug);
        $lineItemsModule = Module::where('slug', 'line_items')->first();
        $lineItemFields = $lineItemsModule?->allFields() ?? collect();

        return Inertia::render('Settings/PdfTemplates/Edit', [
            'template'      => $pdfTemplate,
            'module'        => $moduleModel,
            'fields'        => $fields,
            'relationships' => $relationships,
            'lineItemFields' => $lineItemFields,
        ]);
    }

    public function update(Request $request, PdfTemplate $pdfTemplate)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string|max:500',
            'is_default'         => 'boolean',
            'definition'         => 'required|array',
            'definition.sections' => 'required|array',
        ]);

        if (!empty($validated['is_default'])) {
            PdfTemplate::where('module_slug', $pdfTemplate->module_slug)
                ->where('id', '!=', $pdfTemplate->id)
                ->update(['is_default' => false]);
        }

        $pdfTemplate->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'definition'  => $validated['definition'],
            'is_default'  => $validated['is_default'] ?? false,
        ]);

        return redirect()
            ->route('settings.pdf-templates.edit', $pdfTemplate)
            ->with('success', 'PDF template updated successfully.');
    }

    public function destroy(PdfTemplate $pdfTemplate)
    {
        $pdfTemplate->delete();

        return redirect()
            ->route('settings.pdf-templates.index')
            ->with('success', 'PDF template deleted.');
    }

    public function setDefault(PdfTemplate $pdfTemplate)
    {
        PdfTemplate::where('module_slug', $pdfTemplate->module_slug)
            ->update(['is_default' => false]);

        $pdfTemplate->update(['is_default' => true]);

        return back()->with('success', 'Default template updated.');
    }

    public function preview(Request $request)
    {
        $moduleSlug = $request->input('module_slug');
        $definition = $request->input('definition', ['sections' => []]);

        $moduleModel = Module::query()->where('slug', $moduleSlug)->first();
        abort_if(!$moduleModel, 422, 'Module not found.');

        $fields          = $moduleModel->allFields();
        $lineItemsModule = Module::query()->where('slug', 'line_items')->first();
        $lineItemFields  = $lineItemsModule ? $lineItemsModule->allFields() : collect();

        $sections = $definition['sections'] ?? [];

        return view('pdf.layout-driven', [
            'record'           => $this->buildPreviewRecord($fields),
            'module'           => $moduleSlug,
            'moduleLabel'      => "modules.{$moduleModel->slug}.single_label",
            'fields'           => $fields,
            'company'          => $this->buildPreviewCompany(),
            'renderer'         => app(PdfValueRenderer::class),
            'sections'         => $sections,
            'lineItems'        => $this->buildPreviewLineItems(),
            'relationshipData' => $this->buildPreviewRelationshipData($sections),
            'currency'         => Settings::get('default_currency', 'EUR'),
            'lineItemFields'   => $lineItemFields,
            'isPreview'        => true,
        ]);
    }

    private function buildPreviewRecord(Collection $fields): array
    {
        $record = ['number' => 'PREVIEW-001'];

        foreach ($fields as $field) {
            $record[$field->name] = match ($field->type) {
                'text', 'string'                    => 'Sample Text',
                'email'                             => 'contact@example.com',
                'phone'                             => '+1 555 0100',
                'url'                               => 'https://example.com',
                'number', 'integer'                 => '42',
                'decimal', 'currency'               => 1250.00,
                'percentage'                        => 12.5,
                'date'                              => now()->format('Y-m-d'),
                'datetime'                          => now()->format('Y-m-d H:i'),
                'select', 'status', 'dropdown'      => $this->firstDropdownValue($field),
                'bool', 'boolean', 'checkbox'       => true,
                'address'                           => json_encode(['street' => '123 Example Street', 'postal_code' => '12345', 'city' => 'Berlin', 'state' => 'Berlin', 'country' => 'Germany']),
                'record'                            => 'John Doe',
                'textarea', 'longtext'              => 'Sample description for preview purposes.',
                default                             => 'Sample',
            };
        }

        return $record;
    }

    private function firstDropdownValue(Field $field): string
    {
        $values = $field->dropdown_list?->values ?? [];
        if (empty($values)) {
            return 'Sample';
        }
        $label = $values[0]['label'] ?? '';
        return Str::contains($label, '.') ? __($label) : ($label ?: ($values[0]['value'] ?? 'Sample'));
    }

    private function buildPreviewLineItems(): array
    {
        return [
            [
                'name'            => 'Professional Services',
                'note'            => 'Monthly consulting',
                'unit'            => 'hr',
                'unit_price'      => 85.00,
                'quantity'        => 8,
                'discount'        => 0,
                'tax_rate'        => 19,
                'subtotal'        => 680.00,
                'discount_amount' => 0.00,
                'tax_amount'      => 129.20,
                'total'           => 809.20,
            ],
            [
                'name'            => 'Software License',
                'note'            => '',
                'unit'            => 'pcs',
                'unit_price'      => 250.00,
                'quantity'        => 2,
                'discount'        => 10,
                'tax_rate'        => 19,
                'subtotal'        => 450.00,
                'discount_amount' => 50.00,
                'tax_amount'      => 85.50,
                'total'           => 535.50,
            ],
            [
                'name'            => 'Setup Fee',
                'note'            => '',
                'unit'            => 'flat',
                'unit_price'      => 300.00,
                'quantity'        => 1,
                'discount'        => 0,
                'tax_rate'        => 19,
                'subtotal'        => 300.00,
                'discount_amount' => 0.00,
                'tax_amount'      => 57.00,
                'total'           => 357.00,
            ],
        ];
    }

    private function buildPreviewRelationshipData(array $sections): array
    {
        $data = [];

        foreach ($sections as $section) {
            if (($section['type'] ?? '') !== 'relationship') {
                continue;
            }
            $relName = $section['relationship'] ?? '';
            $columns = $section['columns'] ?? [];
            if (!$relName || empty($columns)) {
                continue;
            }
            $fakeRow = [];
            foreach ($columns as $col) {
                $fakeRow[$col['name']] = 'Sample';
            }
            $data[$relName] = [$fakeRow, $fakeRow];
        }

        return $data;
    }

    private function buildPreviewCompany(): array
    {
        return [
            'name'     => Settings::get('company_name', config('app.name')),
            'address'  => Settings::get('company_address', ''),
            'phone'    => Settings::get('company_phone', ''),
            'email'    => Settings::get('company_email', ''),
            'website'  => Settings::get('company_website', ''),
            'logo_url' => Settings::get('company_logo_url', ''),
        ];
    }
}
