<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecordController extends Controller
{
    public function __invoke(string $module, string $recordId){
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];
        $handler_class = $moduleModel->handler_class ?? "App\Handlers\Modules\\".ucwords($module)."ModuleHandler";


        if (empty($handler_class)) {
          dd("No Handler Class found for module $module");
        }

        if (!class_exists($handler_class)) {
            $props = [];
        } else {
            $handler = app($handler_class);
            
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

    public function create(string $module){
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];

        $handler_class = $moduleModel->handler_class
            ?? "App\\Handlers\\Modules\\" . Str::studly($module) . "ModuleHandler";

        if (class_exists($handler_class)) {
            $handler = app($handler_class);
        }

        $recordLayout = optional($moduleModel->recordLayout())->definition;

        return Inertia::render('Modules/Create', array_merge([
            'module'       => $moduleModel,
            'title'        => $moduleModel->name,
            'recordLayout' => $recordLayout,
        ], $props));
    }

    public function store(Request $request, string $module) {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass  = $moduleModel->model_class;

        $record = $modelClass::create(
            $request->except('_token')
        );

        return redirect("/{$module}/{$record->id}")
            ->with('success', 'Record created successfully.');
    }

    public function update(Request $request, string $module, string $id){
      $moduleModel = Module::where('slug', $module)->firstOrFail();
      $modelClass  = $moduleModel->model_class;

      $record = $modelClass::findOrFail($id);
      $record->fill($request->except('_token', '_method'))->save();

      return back()->with('success', 'Record updated successfully.');
    }

  }




