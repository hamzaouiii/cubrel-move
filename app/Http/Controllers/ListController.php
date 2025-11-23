<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Contracts\ModuleHandler;

class ListController extends Controller
{
public function __invoke(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        // Resolve handler class: prefer DB field, fallback to convention
        $handlerClass = $moduleModel->handler_class
            ?? "App\\Handlers\\Modules\\" . Str::studly($moduleModel->slug) . "ModuleHandler";

        if (empty($handlerClass)) {
            dd("No Handler Class found for module {$moduleModel->slug}");
        }

        $props = [];

        if (class_exists($handlerClass)) {
            $handler = app($handlerClass);

            if ($handler instanceof ModuleHandler && method_exists($handler, 'getListData')) {
                // Collect all request params (search, perPage, etc.)
                $params = request()->all();
                $params['perPage'] = $params['perPage'] ?? request()->query('perPage', 18);

                $props = $handler->getListData($params);
            }
        }

        $listLayout = optional($moduleModel->listLayout())->definition;

        return Inertia::render('Modules/List', array_merge([
            'module'     => $moduleModel,
            'title'      => $moduleModel->name,
            'listLayout' => $listLayout,
            // so Vue can keep search/perPage in the UI
            'filters'    => request()->only(['search', 'perPage']),
        ], $props));
    }
}


