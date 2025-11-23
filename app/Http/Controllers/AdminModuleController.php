<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Contracts\ModuleHandler;

class AdminModuleController extends Controller
{
    public function __invoke(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];
        $handlerClass = $module->handlerClass ?? "App\Handlers\Modules\\".ucwords($module)."ModuleHandler";

        if (empty($handlerClass)) {
            dd("No Handler Class found for module $module");
        }

        if (!class_exists($handlerClass)) {
            $props = [];
        } else {
            $handler = app($handlerClass);
            
            if ($handler instanceof ModuleHandler || method_exists($handler, 'getListData')) {
                $params = $handler instanceof ModuleHandler 
                    ? ['perPage' => request()->query('perPage', 18)]
                    : request()->all();
                    
                $props = $handler->getListData($params);
            } else {
                $props = [];
            }
        }
        $listLayout = optional($moduleModel->listLayout())->definition;


        return Inertia::render('Modules/List', array_merge([
            'module' => $moduleModel,
            'title'  => $moduleModel->name,
            'listLayout' => $listLayout,
        ], $props));
    }
}


