<?php


namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Modules\SettingItem;


class ModuleManagerController extends Controller
{
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

      return Inertia::render('Settings/Page', [
        'item'     => $item,
        'setting_modules' => $modules
      ]);
    }

    public function show(Request $request){
      $id = last($request->segments());
      $module = Module::where('id', $id)->firstOrFail();
        //maybe I'll includse the layouts later
        // ->with([
        //     'layouts' => function ($q) {
        //         $q->orderBy('type')->orderBy('name');
        //     },
        // ])

      return Inertia::render('Settings/Modules/Edit', [
        'module' => $module
      ]);

    }

    public function update(Request $request, $id)
    {
        // Load module config from DB
        $module = Module::where('id', $id)->firstOrFail();

        // Validate
        $data = $request->except('_token', '_method');
        // Save
        $module->fill($data)->save();
        return redirect()->to('/settings/customisation/modules');
    }

    public function create(){
      
      return Inertia::render('Settings/Modules/Create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'label'       => ['nullable', 'string', 'max:255'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'color'       => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug'        => ['required', 'string', 'max:255', 'alpha_dash', 'unique:modules,slug'],
            'show_in_sidebar' => ['boolean'],
        ]);

        // If label is empty, use name
        if (empty($validated['label'])) {
            $validated['label'] = $validated['name'];
        }

        // ----- DEFAULTS -----
        $DEFAULT_ICON          = 'fa-file-lines';
        $DEFAULT_COLOR         = '#000000';
        $DEFAULT_SORT_ORDER    = (Module::max('sort_order') ?? 0) + 1;
        $DEFAULT_IS_ACTIVE     = true;
        $DEFAULT_PERMISSION    = true;
        $DEFAULT_SHOW_SIDEBAR  = true;

        $module = Module::create([
            'slug'        => $validated['slug'],
            'name'        => $validated['name'],
            'label'       => $validated['label'],
            'icon'        => $validated['icon'] ?? $DEFAULT_ICON,
            'color'       => $validated['color'] ?: $DEFAULT_COLOR,
            'path'        => '/' . $validated['slug'],
            'sort_order'  => $DEFAULT_SORT_ORDER,
            'is_active'   => $DEFAULT_IS_ACTIVE,
            'description' => $validated['description'] ?? '',
            'can_view'    => $DEFAULT_PERMISSION,
            'can_create'  => $DEFAULT_PERMISSION,
            'can_edit'    => $DEFAULT_PERMISSION,
            'can_delete'  => $DEFAULT_PERMISSION,
            'model_class' => null,
            'table_name'  => null,
            'show_in_sidebar' => $request->boolean('show_in_sidebar', $DEFAULT_SHOW_SIDEBAR),
        ]);

        return redirect("/settings/customisation/modules/{$module->id}");
    }


      
}
