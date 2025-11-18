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

        if ($handlerClass = $moduleModel->handler_class) {
            if (class_exists($handlerClass)) {
                $handler = app($handlerClass);

                if ($handler instanceof ModuleHandler) {
                  // here implement search later
                    $props = $handler->getListData([
                        'perPage' => request()->query('perPage', 31),
                    ]);
                } else {
                    if (method_exists($handler, 'getListData')) {
                        $props = $handler->getListData(request()->all());
                    } else {
                        $props = [];
                    }
                }
            } else {
                // else
            }
        }
        $listLayout = optional($moduleModel->listLayout())->definition;


        return Inertia::render('Admin/Modules/List', array_merge([
            'module' => $moduleModel,
            'title'  => $moduleModel->name,
            'listLayout' => $listLayout,
        ], $props));
    }
}


