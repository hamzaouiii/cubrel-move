<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use Inertia\Inertia;
use App\Services\Relationships\RelationshipService;

class LayoutManagerController extends Controller
{
  public function store(Request $request, \App\Models\Module $module, string $layoutType)
  {
    $validated = [];
    if ($layoutType == 'list' || $layoutType === "linkingPanel") {
      $validated = $request->validate([
        'definition' => 'required|array',
        'definition.columns' => 'required|array',
      ]);
    } else if ($layoutType == 'record') {
      $validated = $request->validate([
        'definition' => 'required|array',
        'definition.sections' => 'required|array'
      ]);
    } else if ($layoutType == 'related') {
      $validated = $request->validate([
        'definition' => 'required|array',
        'definition.columns' => 'required|array'
      ]);
    }

    $layout = \App\Models\Layout::firstOrNew([
      'module_id' => $module->id,
      'type'      => $layoutType,
    ]);


    $layout->module_id   = $module->id;
    $layout->module_name = $module->name;
    $layout->type        = $layoutType;
    $layout->definition  = $validated['definition'];
    $layout->save();

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
    $fields = $module->fields;
    $relationships = RelationshipService::getRelationshipForModule($module->slug);
    return Inertia::render('Settings/Layouts/Edit', [
      'module' => $module,
      'type'  => $type,
      'defaultLayout' => $layout,
      'fields'   => $fields,
      'relationships' => $relationships
    ]);
  }
}
