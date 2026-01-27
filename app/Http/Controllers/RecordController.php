<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DropdownList;

class RecordController extends Controller
{
  public function __invoke(string $module, string $recordId)
  {
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $props = [];
    $handler_class = $moduleModel->handler_class ?? "App\Handlers\Modules\\" . ucwords($module) . "ModuleHandler";


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
    $recordLayout = $moduleModel->recordLayout();
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->fields;
    return Inertia::render('Modules/Record', array_merge([
      'module'   => $moduleModel,
      'title'    => $moduleModel->name,
      'recordId' => $recordId,
      'recordLayout' => $recordLayout,
      'dropdownLists' => $recorddropdownLists,
      'fields' => $fields,

    ], $props));
  }

  public function create(string $module)
  {
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

    $recordLayout = $moduleModel->recordLayout();

    return Inertia::render('Modules/Create', array_merge([
      'module'       => $moduleModel,
      'title'        => $moduleModel->name,
      'recordLayout' => $recordLayout,
    ], $props));
  }

  public function store(Request $request, string $module)
  {
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

  public function destroy(string $module, int|string $record)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass  = $moduleModel->model_class;

    $model = $modelClass::findOrFail($record);

    $model->delete();

    return redirect()
      ->route('modules.index', $module)
      ->with('success', __('modules.actions.delete_success'));
  }

  public function destroyMany(Request $request, string $module)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass  = $moduleModel->model_class;

    $handlerClass = $moduleModel->handler_class
      ?? "App\\Handlers\\Modules\\" . Str::studly($moduleModel->slug) . "ModuleHandler";

    $selectedIds = $request->input('selectedIds', []);
    $allMatchingSelected = (bool) $request->input('allMatchingSelected', false);
    $filters = (array) $request->input('filters', []);

    if (!class_exists($modelClass)) {
      abort(500, "Model class not found for module {$moduleModel->slug}");
    }

    $selectedIds = array_values(array_filter($selectedIds, fn($id) => $id !== null && $id !== ''));

    if (!$allMatchingSelected && count($selectedIds) === 0) {
      return back()->with('error', 'No records selected.');
    }


    $baseQuery = $modelClass::query();

    if (class_exists($handlerClass)) {
      $handler = app($handlerClass);
    }

    if ($allMatchingSelected) {
      $search = trim((string) Arr::get($filters, 'search', ''));

      if ($search !== '') {

        $columnsToTry = ['name', 'email', 'description'];

        $table = (new $modelClass)->getTable();
        $existing = array_filter($columnsToTry, function ($col) use ($modelClass) {
          try {
            return Schema::hasColumn((new $modelClass)->getTable(), $col);
          } catch (\Throwable $e) {
            return false;
          }
        });

        if (!empty($existing)) {
          $baseQuery->where(function ($q) use ($existing, $search) {
            foreach ($existing as $col) {
              $q->orWhere($col, 'like', "%{$search}%");
            }
          });
        }
      }

      $count = 0;
      DB::transaction(function () use ($baseQuery, &$count) {
        $baseQuery->select('id')->chunkById(500, function ($chunk) use (&$count) {
          $ids = $chunk->pluck('id')->all();
          $count += count($ids);
          if (!empty($ids)) {
            (clone $chunk)->first()->newQuery()->whereIn('id', $ids)->delete();
          }
        });
      });

      return back()->with('success', "{$count} records deleted.");
    }

    $deleted = $modelClass::whereIn('id', $selectedIds)->delete();

    return back()->with('success', "{$deleted} records deleted.");
  }
}
