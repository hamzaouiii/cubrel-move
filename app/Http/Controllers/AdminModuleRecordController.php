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

        if ($handlerClass = $moduleModel->handler_class) {
            if (class_exists($handlerClass)) {
                $handler = app($handlerClass);

                if ($handler instanceof ModuleHandler && method_exists($handler, 'getRecordData')) {
                    $props = $handler->getRecordData($recordId, request()->all());
                } elseif (method_exists($handler, 'getRecordData')) {
                    $props = $handler->getRecordData($recordId, request()->all());
                } else {
                    $props = [
                        'recordId' => $recordId,
                    ];
                }
            } else {
                $props = [
                    'recordId' => $recordId,
                ];
            }
        } else {
            $props = [
                'recordId' => $recordId,
            ];
        }

        // 3) Render a dedicated "Show" page for a single record
        return Inertia::render('Admin/Modules/Record', array_merge([
            'module'   => $moduleModel,
            'title'    => $moduleModel->name,
            'recordId' => $recordId,
        ], $props));
    }
}
