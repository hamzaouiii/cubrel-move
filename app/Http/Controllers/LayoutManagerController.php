<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\Relationships\RelationshipService;
use Inertia\Inertia;

class LayoutManagerController extends Controller
{
    public function store(\Illuminate\Http\Request $request, Module $module, string $layoutType)
    {
        $validated = [];
        if ($layoutType == 'list' || $layoutType === 'linkingPanel') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.columns' => 'required|array',
            ]);
        } elseif ($layoutType == 'record') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.sections' => 'required|array',
            ]);

            $this->assertRequiredFieldsPresent($module, $validated['definition']['sections']);
        } elseif ($layoutType == 'related') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.columns' => 'required|array',
            ]);
        } elseif ($layoutType == 'lineItemsSnapshot') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.fields' => 'required|array',
                'definition.fields.*.name' => 'required|string',
                'definition.fields.*.source_field' => 'nullable|string',
            ]);
        } else {
            abort(422, "Unknown layout type [{$layoutType}].");
        }

        $layout = \App\Models\Layout::firstOrNew([
            'module_id' => $module->id,
            'type' => $layoutType,
        ]);

        $layout->module_id = $module->id;
        $layout->module_name = $module->slug;
        $layout->type = $layoutType;
        $layout->definition = $validated['definition'];
        $layout->save();

        return redirect()
            ->route('settings.modules.layouts.edit', [$module->id, $layoutType])
            ->with('success', __('layouts.layout_update_success'));
    }

    /**
     * A field marked required must always be reachable on the record form, or
     * a create/update could silently (or fatally) omit it.
     */
    protected function assertRequiredFieldsPresent(Module $module, array $sections): void
    {
        $requiredFields = $module->allFields()
            ->filter(fn ($field) => $field->required && ! $field->readonly && ! $field->is_calculated);

        $layoutFieldNames = collect($sections)
            ->filter(fn ($section) => empty($section['has_line_items']) && empty($section['has_attendees']))
            ->flatMap(fn ($section) => collect($section['layout'] ?? [])->pluck('name'))
            ->filter();

        $missing = $requiredFields->reject(fn ($field) => $layoutFieldNames->contains($field->name));

        if ($missing->isNotEmpty()) {
            $labels = $missing->map(fn ($field) => $this->translateFieldLabel($field));

            throw \Illuminate\Validation\ValidationException::withMessages([
                'definition.sections' => __('layouts.required_fields_missing', ['fields' => $labels->implode(', ')]),
            ]);
        }
    }

    protected function translateFieldLabel(\App\Models\Field $field): string
    {
        if (! $field->label) {
            return $field->name;
        }

        $custom = \App\Models\Label::where('key', $field->label)->value('value');
        if ($custom) {
            return $custom;
        }

        $translated = __($field->label);

        return $translated === $field->label ? $field->label : $translated;
    }

    public function show(string $id)
    {
        $module = Module::query()->where('id', $id)
            ->with([
                'layouts' => function ($q) {
                    $q->orderBy('type')->orderBy('name');
                },
            ])
            ->firstOrFail();

        return Inertia::render('Settings/Layouts/Record', ['module' => $module]);
    }

    public function edit(string $id, string $type)
    {
              if (!in_array($type ,['list', 'linkingPanel', 'record', 'related', 'lineItemsSnapshot'])) {
                abort(404);
              }

        $module = Module::query()->where('id', $id)
            ->with([
                'layouts' => function ($q) {
                    $q->orderBy('type')->orderBy('name');
                },
            ])->firstOrFail();
        $layout = $module->getDefaultLayout($type);
        $fields = $module->allFields();
        $line_itemsModel = Module::query()
            ->where('slug', 'line_items')
            ->firstOrFail();

        $lineItemFields = $line_itemsModel->allFields()
            ->whereNotIn('name', ['parent_id', 'parent_type'])
            ->values();

        $relationships = RelationshipService::getRelationshipForModule($module->slug);

        $sourceModuleFields = collect();
        if ($type === 'lineItemsSnapshot' && $module->lineItemSourceModuleSlug()) {
            $sourceModule = Module::query()
                ->where('slug', $module->lineItemSourceModuleSlug())
                ->first();
            $sourceModuleFields = $sourceModule?->allFields() ?? collect();
        }

        return Inertia::render('Settings/Layouts/Edit', [
            'module' => $module,
            'type' => $type,
            'defaultLayout' => $layout,
            'fields' => $fields,
            'relationships' => $relationships,
            'lineItemFields' => $lineItemFields,
            'sourceModuleFields' => $sourceModuleFields,

        ]);
    }
}
