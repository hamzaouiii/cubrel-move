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
                // needs relations on Module model:
                // public function layouts() { return $this->hasMany(Layout::class); }
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
      
}
