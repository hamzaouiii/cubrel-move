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
    $recordLayout = $moduleModel->recordLayout();
    $relatedLayout = $moduleModel->relatedLayout();
    $fields = $moduleModel->allFields();
    return Inertia::render('Modules/Record', array_merge([
      'module'   => $moduleModel,
      'title'    => $moduleModel->name,
      'recordId' => $recordId,
      'overviewLayout' => $recordLayout,
      'relatedLayout' => $relatedLayout,
      'fields' => $fields
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
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->allFields();
    return Inertia::render('Modules/Create', array_merge([
      'module'       => $moduleModel,
      'title'        => $moduleModel->name,
      'recordLayout' => $recordLayout,
      'dropdownLists' => $recorddropdownLists,
      'fields' => $fields,
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

  // public function updateMany(Request $request, string $module)
  // {
  //   $moduleModel = Module::where('slug', $module)->firstOrFail();
  //   $modelClass  = $moduleModel->model_class;

  //   $data      = $request->input('data', []);
  //   $field_key   = $data['field'] ?? null;
  //   $newValue  = $data['value'] ?? null;
  //   $field = Field::query()
  //     ->where('key', $field_key)
  //     ->first();

  //   if (!$field) {
  //     return back()->with('error', 'No field specified for update.');
  //   }

  //   // 2. Extract selection logic
  //   $selectedIds = $data['selectedIds'] ?? [];
  //   $allMatchingSelected = (bool) ($data['allMatchingSelected'] ?? false);
  //   $filters = (array) ($data['filters'] ?? []);

  //   if (!class_exists($modelClass)) {
  //     abort(500, "Model class not found for module {$moduleModel->slug}");
  //   }

  //   $selectedIds = array_values(array_filter($selectedIds, fn($id) => $id !== null && $id !== ''));

  //   if (!$allMatchingSelected && count($selectedIds) === 0) {
  //     return back()->with('error', 'No records selected.');
  //   }

  //   $baseQuery = $modelClass::query();


  //   // 3. Handle "Select All" with Filters
  //   if ($allMatchingSelected) {
  //     $search = trim((string) Arr::get($filters, 'search', ''));

  //     if ($search !== '') {
  //       $columnsToTry = ['name', 'email', 'description'];
  //       $existing = array_filter($columnsToTry, function ($col) use ($modelClass) {
  //         return Schema::hasColumn((new $modelClass)->getTable(), $col);
  //       });

  //       if (!empty($existing)) {
  //         $baseQuery->where(function ($q) use ($existing, $search) {
  //           foreach ($existing as $col) {
  //             $q->orWhere($col, 'like', "%{$search}%");
  //           }
  //         });
  //       }
  //     }

  //     $count = 0;
  //     DB::transaction(function () use ($baseQuery, $field, $newValue, &$count, $modelClass) {
  //       // Using chunkById to handle large datasets efficiently
  //       $baseQuery->select('id')->chunkById(500, function ($chunk) use ($field, $newValue, &$count, $modelClass) {
  //         $ids = $chunk->pluck('id')->all();
  //         if (!empty($ids)) {
  //           // Update only the records in the current chunk
  //           $count += $modelClass::whereIn('id', $ids)->update([$field => $newValue]);
  //         }
  //       });
  //     });
  //     return back();
  //   }
  //   if ($field->is_custom) {
  //     $updatedCount = 0;
  //     $field_name = $field->name;

  //     $records =  $modelClass::whereIn('id', $selectedIds)
  //       ->get();

  //     $records->each(function ($record) use ($field_name, $newValue, &$updatedCount) {
  //       $record->{$field_name} = $newValue;
  //       if ($record->save()) {
  //         $updatedCount++;
  //       }
  //     });
  //   } else {
  //     $field_name = $field->name;
  //     $updatedCount = $modelClass::whereIn('id', $selectedIds)->update([$field_name => $newValue]);
  //   }
  //   return back()->with('success', "{$updatedCount} records updated.");
  // }

  public function updateMany(Request $request, string $module)
  {
    $moduleModel = Module::where('slug', $module)->firstOrFail();
    $modelClass  = $moduleModel->model_class;
    $modelHandler = $moduleModel->handler_class;


    if (class_exists($modelHandler)) {
      $handler = app($modelHandler);
    } else {
      throw new ModuleHandlerNotFoundException(
        "Handler class [{$modelHandler}] not found for module [{$module}]"
      );
    }

    if (!class_exists($modelClass)) {
      throw new ModuleHandlerNotFoundException(
        "Model class not found for module {$moduleModel->slug}"
      );
    }

    $field_key   = $request->field ?? null;
    $newValue  = $request->value ?? null;


    $field = Field::query()
      ->where('key', $field_key)
      ->first();
    $field_name = $field->name;

    if (!$field) {
      return back()->with('error', 'No field specified for update.');
    }

    $selectedIds = $request->selectedIds ?? [];
    $allMatchingSelected = (bool) ($request->allMatchingSelected ?? false);
    $filters = (array) ($request->filters ?? []);

    $selectedIds = array_values(array_filter($selectedIds, fn($id) => $id !== null && $id !== ''));

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

      $count = 0;
      DB::transaction(function () use ($baseQuery, $field, $newValue, &$count, $modelClass) {
        // Using chunkById to handle large datasets efficiently
        $baseQuery->select('id')->chunkById(500, function ($chunk) use ($field, $newValue, &$count, $modelClass) {
          $ids = $chunk->pluck('id')->all();
          if (!empty($ids)) {
            // Update only the records in the current chunk
            if ($field->is_custom) {
              $count += $modelClass::whereIn('id', $ids)->update(["custom_fields->{$field->name}" => $newValue]);
            } else {
              $count += $modelClass::whereIn('id', $ids)->update([$field->name => $newValue]);
            }
          }
        });
      });
      return back();
    }
    // Handle Custom vs Standard Fields natively via the database
    if ($field->is_custom) {
      // Use Laravel's JSON arrow syntax to update a specific key inside the custom_fields column
      $updatedCount = $modelClass::whereIn('id', $selectedIds)
        ->update(["custom_fields->{$field_name}" => $newValue]);
    } else {
      // Standard column update
      $updatedCount = $modelClass::whereIn('id', $selectedIds)
        ->update([$field_name => $newValue]);
    }

    return back()->with('success', "{$updatedCount} records updated.");
  }
}
