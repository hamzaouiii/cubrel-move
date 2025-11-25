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
        'modules' => $modules
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

      
}
