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

    $recordLayout = optional($moduleModel->recordLayout())->definition;
    return Inertia::render('Modules/Record', array_merge([
      'module'   => $moduleModel,
      'title'    => $moduleModel->name,
      'recordId' => $recordId,
      'recordLayout' => $recordLayout
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

    $recordLayout = optional($moduleModel->recordLayout())->definition;

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

    // resolve handler (same style as ListController)
    $handlerClass = $moduleModel->handler_class
      ?? "App\\Handlers\\Modules\\" . Str::studly($moduleModel->slug) . "ModuleHandler";

    $selectedIds = $request->input('selectedIds', []);
    $allMatchingSelected = (bool) $request->input('allMatchingSelected', false);
    $filters = (array) $request->input('filters', []);

    // Basic safety
    if (!class_exists($modelClass)) {
      abort(500, "Model class not found for module {$moduleModel->slug}");
    }

    // Ensure array of ints/strings
    $selectedIds = array_values(array_filter($selectedIds, fn($id) => $id !== null && $id !== ''));

    // Nothing selected and not in "all matching" mode
    if (!$allMatchingSelected && count($selectedIds) === 0) {
      return back()->with('error', 'No records selected.');
    }

    // Build a base query:
    // - If handler supports query($params), reuse it so search/filter logic stays consistent.
    // - Otherwise fall back to model::query().
    $baseQuery = $modelClass::query();

    if (class_exists($handlerClass)) {
      $handler = app($handlerClass);

      // if your module handlers extend BasePaginatedModuleHandler, they have query($params)
      if ($handler instanceof ModuleHandler && method_exists($handler, 'query')) {
        // IMPORTANT: query() in your BasePaginatedModuleHandler is protected,
        // so you can't call it directly. If you want reuse, see note below.
        // For now, use handler->getListData() style filters only if you expose a method.
      }
    }

    // Apply filters (currently only "search" because that's what your UI sends)
    // If you add more filters later, extend this part.
    if ($allMatchingSelected) {
      $search = trim((string) Arr::get($filters, 'search', ''));

      if ($search !== '') {
        // Option A: if model has a $searchable definition
        // Option B: use module layout columns as search fields
        // For now: use a conservative default: search across all string-like columns is expensive.
        // Best: store searchable fields per module in DB or handler.
        //
        // Minimal approach: try description/name/email if they exist:
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

      // Delete all matching (filtered) records
      $count = 0;
      DB::transaction(function () use ($baseQuery, &$count) {
        // chunk to avoid loading too much into memory
        $baseQuery->select('id')->chunkById(500, function ($chunk) use (&$count) {
          $ids = $chunk->pluck('id')->all();
          $count += count($ids);
          if (!empty($ids)) {
            // bulk delete by ids
            (clone $chunk)->first()->newQuery()->whereIn('id', $ids)->delete();
          }
        });
      });

      return back()->with('success', "{$count} records deleted.");
    }

    // Normal mode: delete only selected ids
    $deleted = $modelClass::whereIn('id', $selectedIds)->delete();

    return back()->with('success', "{$deleted} records deleted.");
  }
}
