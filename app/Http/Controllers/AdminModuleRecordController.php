<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
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


        return Inertia::render('Modules/Record', array_merge([
            'module'   => $moduleModel,
            'title'    => $moduleModel->name,
            'recordId' => $recordId,
            'recordLayout' => $recordLayout
        ], $props));
    }

    public function update(Request $request, $module, $id)
    {
        // Load module config from DB
        $module = Module::where('slug', $module)->firstOrFail();

        // Resolve model dynamically
        $modelClass = $module->model_class;

        // Load record
        $record = $modelClass::findOrFail($id);
        // Validate
$data = $request->except('_token', '_method');
        // Save
        $record->fill($data)->save();

        return back()->with('success', 'Record updated successfully.');
    }
  }




