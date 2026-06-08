<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\PdfTemplate;
use App\Services\Relationships\RelationshipService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LayoutManagerController extends Controller
{
    public function store(Request $request, Module $module, string $layoutType)
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
        } elseif ($layoutType == 'related') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.columns' => 'required|array',
            ]);
        } elseif ($layoutType == 'pdf') {
            $validated = $request->validate([
                'definition' => 'required|array',
                'definition.sections' => 'required|array',
            ]);
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

        if ($layoutType === 'pdf') {
            PdfTemplate::updateOrCreate(
                ['layout_id' => $layout->id],
                [
                    'module_slug' => $module->slug,
                    'name' => $module->name,
                    'blade_view' => 'pdf.layout-driven',
                    'is_default' => true,
                ]
            );
        }

        return redirect()
            ->route('settings.modules.layouts.edit', [$module->id, $layoutType])
            ->with('success', __('layouts.layout_update_success'));
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

    public function edit(Request $request, string $id, string $type)
    {
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

        $lineItemFields = $line_itemsModel->allFields();

        $relationships = RelationshipService::getRelationshipForModule($module->slug);

        return Inertia::render('Settings/Layouts/Edit', [
            'module' => $module,
            'type' => $type,
            'defaultLayout' => $layout,
            'fields' => $fields,
            'relationships' => $relationships,
            'lineItemFields' => $lineItemFields,

        ]);
    }
}
