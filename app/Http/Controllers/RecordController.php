<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecordController extends Controller
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

    // public function update(Request $request, $module, $id)
    // {
    //     $module = Module::where('slug', $module)->firstOrFail();

    //     $modelClass = $module->model_class;

    //     $record = $modelClass::findOrFail($id);
    //     $data = $request->except('_token', '_method');
    //     $record->fill($data)->save();

    //     return back()->with('success', 'Record updated successfully.');
    // }
    public function create(string $module)
    {
        $moduleModel = Module::query()
            ->where('slug', $module)
            ->where('is_active', true)
            ->firstOrFail();

        $props = [];

        $handlerClass = $moduleModel->handler_class
            ?? "App\\Handlers\\Modules\\" . Str::studly($module) . "ModuleHandler";

        if (class_exists($handlerClass)) {
            $handler = app($handlerClass);

            // if (method_exists($handler, 'getCreateData')) {
            //     $props = $handler->getCreateData(request()->all());
            // }
        }

        $recordLayout = optional($moduleModel->recordLayout())->definition;

        return Inertia::render('Modules/Create', array_merge([
            'module'       => $moduleModel,
            'title'        => $moduleModel->name,
            'recordId'     => null,
            'recordLayout' => $recordLayout,
        ], $props));
    }

      public function store(Request $request, string $module) {
        // dd($request->all());
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass  = $moduleModel->model_class;

        $record = $modelClass::create(
            $request->except('_token')
        );

        return redirect("/{$module}/{$record->id}")
            ->with('success', 'Record created successfully.');
    }

        public function update(Request $request, string $module, string $id)
    {
        $moduleModel = Module::where('slug', $module)->firstOrFail();
        $modelClass  = $moduleModel->model_class;

        $record = $modelClass::findOrFail($id);
        $record->fill($request->except('_token', '_method'))->save();

        return back()->with('success', 'Record updated successfully.');
    }

  }




