<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use App\Contracts\ModuleHandler;

class AdminModuleRecordController extends Controller
{
    public function __invoke(string $module, string $recordId)
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
            
            if ($handler instanceof ModuleHandler || method_exists($handler, 'getRecordData')) {
                $props = $handler->getRecordData($recordId, request()->all());                    
            } else {
            $props = ['recordId' => $recordId];
            }
          }

        $recordLayout = optional($moduleModel->recordLayout())->definition;


        return Inertia::render('Admin/Modules/Record', array_merge([
            'module'   => $moduleModel,
            'title'    => $moduleModel->name,
            'recordId' => $recordId,
            'recordLayout' => $recordLayout
        ], $props));
    }

  }




