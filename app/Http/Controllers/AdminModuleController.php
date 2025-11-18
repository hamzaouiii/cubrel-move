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

        // Try to resolve a handler class defined on the module record
        if ($handlerClass = $moduleModel->handler_class) {
            if (class_exists($handlerClass)) {
                $handler = app($handlerClass);

                // If it implements the ModuleHandler contract, use getListData
                if ($handler instanceof ModuleHandler) {
                    $props = $handler->getListData([
                        // pass any params you want, e.g. request filters/pagination
                        'perPage' => request()->query('perPage', 31),
                    ]);
                } else {
                    // Optionally attempt to call a known method 'getListData' even if contract not implemented
                    if (method_exists($handler, 'getListData')) {
                        $props = $handler->getListData(request()->all());
                    } else {
                        // fallback: nothing or log
                        $props = [];
                    }
                }
            } else {
                // handler_class specified but class missing — you may want to log or throw
                // logger()->warning("Module handler class {$handlerClass} not found for module {$moduleModel->slug}");
            }
        }

        // Optional fallback: try to call a controller index method
        if (empty($props)) {
            // e.g. App\Http\Controllers\Admin\LeadController@index
            $controllerClass = $moduleModel->controller_class ?? null;
            if ($controllerClass && class_exists($controllerClass)) {
                try {
                    // Use container to call, it will inject Request if needed
                    $response = app()->call([$controllerClass, 'index'], ['module' => $moduleModel->slug]);
                    // If controller returns array or JsonResponse etc. adapt accordingly
                    if (is_array($response)) {
                        $props = $response;
                    } elseif (method_exists($response, 'getData')) {
                        $props = (array) $response->getData();
                    }
                } catch (\Throwable $e) {
                    // swallow or log — don't break the admin listing page
                    // logger()->error($e);
                }
            }
        }

        return Inertia::render('Admin/Modules/List', array_merge([
            'module' => $moduleModel,
            'title'  => $moduleModel->name,
        ], $props));
    }
}
