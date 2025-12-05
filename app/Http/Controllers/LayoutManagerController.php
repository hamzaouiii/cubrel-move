<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Layout;
use Inertia\Inertia;
use App\Models\Settings\SettingItem;

class LayoutManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = Module::query()
        ->with([
            'layouts' => function ($q) {
                $q->orderBy('type')->orderBy('name');
            },
        ])
        ->orderBy('id')
        ->get();
      $item = SettingItem::where('path', 'like', '%' . $request->path())->first();
 
      return Inertia::render('Settings/Layouts/List', [
        'item'     => $item,
        'setting_modules' => $modules
      ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
      public function store(Request $request, \App\Models\Module $module, string $layoutType)
      {
          // Validate incoming request
          $validated = $request->validate([
              'definition' => 'required|array',
              'definition.columns' => 'required|array',
          ]);

          // Find existing layout or create new
          $layout = \App\Models\Layout::firstOrNew([
              'module_id' => $module->id,
              'type'      => $layoutType,
          ]);

          // Assign values
          $layout->module_id   = $module->id;
          $layout->module_name = $module->name; // keep convention from your model
          $layout->type        = $layoutType;
          $layout->definition  = $validated['definition'];

          $layout->save();

          return redirect()
              ->route('settings.layouts.edit', [$module->id, $layoutType])
              ->with('success', __('Layout updated successfully.'));
      }



    /**
     * Display the specified resource.
     */
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id, string $type)
    {
      $module = Module::query()->where('id', $id)
       ->with([
            'layouts' => function ($q) {
                $q->orderBy('type')->orderBy('name');
            },
        ])->firstOrFail();
        $item = SettingItem::where('path', 'like', '%' . $request->path())->first();
        $defaultLayout = Layout::getDefaultLayout($type);
        $fields = $module->fields();


        return Inertia::render('Settings/Layouts/Edit', [
          'item'     => $item,
          'module' => $module,
          'type'  => $type,
          'defaultLayout' => $defaultLayout,
          'fields'   => $fields
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
