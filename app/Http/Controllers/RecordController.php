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
use App\Exceptions\ModuleHandlerNotFoundException;
use App\Models\Field;

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
      throw new ModuleHandlerNotFoundException(
        "Handler class [{$handler_class}] not found for module [{$module}]. Please check if the file exists or re-deploy."
      );
    }

    if (!class_exists($handler_class)) {
      $props = [];
    } else {
      $handler = app($handler_class);

      if ($handler instanceof ModuleHandler || method_exists($handler, 'getRecordData')) {
        $props = $handler->getRecordData($module, $recordId, request()->all());
      } else {
        $props = ['recordId' => $recordId];
      }
    }

    $recordLayout  = $moduleModel->recordLayout();
    $relatedLayout = $moduleModel->relatedLayout();
    $fields        = $moduleModel->allFields();

    return Inertia::render('Modules/Record', array_merge([
      'module'         => $moduleModel,
      'title'          => $moduleModel->name,
      'recordId'       => $recordId,
      'overviewLayout' => $recordLayout,
      'relatedLayout'  => $relatedLayout,
      'fields'         => $fields,
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

    $recordLayout        = $moduleModel->recordLayout();
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields              = $moduleModel->allFields();

    return Inertia::render('Modules/Create', array_merge([
      'module'        => $moduleModel,
      'title'         => $moduleModel->name,
      'recordLayout'  => $recordLayout,
      'dropdownLists' => $recorddropdownLists,
      'fields'        => $fields,
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
    $record->fill($request->except('_token', '_method', 'related'))->save();

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

  // ─────────────────────────────────────────────────────────────────────────
  // destroyMany
  //
  // Accepts three selection modes:
  //
  //   1. Explicit list  — allMatchingSelected=false, selectedIds=[1,2,3]
  //   2. All matching   — allMatchingSelected=true,  excludedIds=[]
  //   3. All except     — allMatchingSelected=true,  excludedIds=[4,5]
  // ─────────────────────────────────────────────────────────────────────────
  public function destroyMany(Request $request, string $module)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass  = $moduleModel->model_class;

    $handlerClass = $moduleModel->handler_class
      ?? "App\\Handlers\\Modules\\" . Str::studly($moduleModel->slug) . "ModuleHandler";

    $selectedIds         = $this->cleanIds($request->input('selectedIds', []));
    $excludedIds         = $this->cleanIds($request->input('excludedIds', []));
    $allMatchingSelected = (bool) $request->input('allMatchingSelected', false);
    $filters             = (array) $request->input('filters', []);

    if (!class_exists($modelClass)) {
      abort(500, "Model class not found for module {$moduleModel->slug}");
    }

    if (!$allMatchingSelected && count($selectedIds) === 0) {
      return back()->with('error', 'No records selected.');
    }

    $baseQuery = $modelClass::query();

    if ($allMatchingSelected) {
      // Apply search filter if present
      if (class_exists($handlerClass)) {
        $handler    = app($handlerClass);
        $searchable = $handler->getSearchableColumns($moduleModel);
      } else {
        $searchable = ['name', 'email', 'description'];
      }

      $search = trim((string) Arr::get($filters, 'search', ''));
      if ($search !== '') {
        $existing = array_filter($searchable, fn($col) => Schema::hasColumn((new $modelClass)->getTable(), $col));
        if (!empty($existing)) {
          $baseQuery->where(function ($q) use ($existing, $search) {
            foreach ($existing as $col) {
              $q->orWhere($col, 'like', "%{$search}%");
            }
          });
        }
      }

      // Exclude explicitly de-selected records
      if (!empty($excludedIds)) {
        $baseQuery->whereNotIn('id', $excludedIds);
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

    // Explicit list mode
    $deleted = $modelClass::whereIn('id', $selectedIds)->delete();

    return back()->with('success', "{$deleted} records deleted.");
  }

  // ─────────────────────────────────────────────────────────────────────────
  // updateMany
  //
  // Same three-mode selection as destroyMany.
  // ─────────────────────────────────────────────────────────────────────────
  public function updateMany(Request $request, string $module)
  {
    $moduleModel  = Module::where('slug', $module)->firstOrFail();
    $modelClass   = $moduleModel->model_class;
    $modelHandler = $moduleModel->handler_class;

    if (!class_exists($modelClass)) {
      throw new ModuleHandlerNotFoundException(
        "Model class not found for module {$moduleModel->slug}"
      );
    }

    if (class_exists($modelHandler)) {
      $handler = app($modelHandler);
    } else {
      throw new ModuleHandlerNotFoundException(
        "Handler class [{$modelHandler}] not found for module [{$module}]"
      );
    }

    $field_key = $request->field ?? null;
    $newValue  = $request->value ?? null;

    $field = Field::query()->where('key', $field_key)->first();

    if (!$field) {
      return back()->with('error', 'No field specified for update.');
    }

    $field_name          = $field->name;
    $selectedIds         = $this->cleanIds($request->input('selectedIds', []));
    $excludedIds         = $this->cleanIds($request->input('excludedIds', []));
    $allMatchingSelected = (bool) ($request->allMatchingSelected ?? false);
    $filters             = (array) ($request->filters ?? []);

    if (!$allMatchingSelected && count($selectedIds) === 0) {
      return back()->with('error', 'No records selected.');
    }

    $baseQuery = $modelClass::query();

    if ($allMatchingSelected) {
      $search = trim((string) Arr::get($filters, 'search', ''));

      if ($search !== '') {
        $searchable = $handler->getSearchableColumns($moduleModel);
        if (!empty($searchable)) {
          $baseQuery->where(function ($q) use ($searchable, $search) {
            foreach ($searchable as $column) {
              $q->orWhere($column, 'like', "%{$search}%");
            }
          });
        }
      }

      // Exclude explicitly de-selected records
      if (!empty($excludedIds)) {
        $baseQuery->whereNotIn('id', $excludedIds);
      }

      $count = 0;
      DB::transaction(function () use ($baseQuery, $field, $newValue, &$count, $modelClass) {
        $baseQuery->select('id')->chunkById(500, function ($chunk) use ($field, $newValue, &$count, $modelClass) {
          $ids = $chunk->pluck('id')->all();
          if (!empty($ids)) {
            $column = $field->is_custom
              ? "custom_fields->{$field->name}"
              : $field->name;

            $count += $modelClass::whereIn('id', $ids)->update([$column => $newValue]);
          }
        });
      });

      return back()->with('success', "{$count} records updated.");
    }

    // Explicit list mode
    if ($field->is_custom) {
      $updatedCount = $modelClass::whereIn('id', $selectedIds)
        ->update(["custom_fields->{$field_name}" => $newValue]);
    } else {
      $updatedCount = $modelClass::whereIn('id', $selectedIds)
        ->update([$field_name => $newValue]);
    }

    return back()->with('success', "{$updatedCount} records updated.");
  }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

  /**
   * Strip null / empty-string values from an ID array coming from the frontend.
   */
  private function cleanIds(mixed $input): array
  {
    return array_values(
      array_filter((array) $input, fn($id) => $id !== null && $id !== '')
    );
  }
}
