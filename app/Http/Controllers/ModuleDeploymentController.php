<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Services\ModuleScaffolder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;

class ModuleDeploymentController extends Controller
{
  // Call #1: Validation & DB Update
  public function initialize(Request $request, Module $module)
  {
    // 1. Pre-flight Infrastructure Check
    $this->ensureDirectoryIsReady();

    // 2. Validation
    $validated = $request->validate([
      'display_label'   => ['required', 'string', 'max:255'],
      'single_label'    => ['required', 'string', 'max:255'],
      'slug'            => ['required', 'string', 'max:255', 'alpha_dash', Rule::notIn(config('reserved_keywords.slugs')), 'unique:modules,slug,' . $module->id],
      'module_category_id' => ['required', 'string', 'exists:module_categories,id'],
      'icon'            => ['nullable', 'string'],
      'color'           => ['nullable', 'string'],
      'description'     => ['nullable', 'string'],
      'show_in_sidebar' => ['boolean'],
    ]);

    // 3. Data Preparation
    $baseName = Str::studly($validated['slug']);
    $tableName = "cstm_" . Str::snake($validated['slug']);

    // 4. Update Module (Keep it as a DRAFT for now)
    $module->update([
      'name'            => $validated['display_label'],
      'slug'            => $validated['slug'],
      'icon'            => $validated['icon'] ?? 'fa-solid fa-bahai',
      'color'           => $validated['color'] ?? '#000000',
      'description'     => $validated['description'] ?? '',
      'show_in_sidebar' => $validated['show_in_sidebar'] ?? true,
      'module_category_id' => $validated['module_category_id'],
      'sort_order'      => $module->sort_order ?? (Module::max('sort_order') + 1),
      'handler_class'   => "App\\Handlers\\Modules\\Custom\\{$baseName}ModuleHandler",
      'model_class'     => "App\\Models\\Modules\\Custom\\{$baseName}",
      'table_name'      => $tableName,
      'path'            => '/' . $validated['slug'],
      'is_draft'        => true,  // Keep as draft during deploy
      'is_active'       => false, // Don't show to users yet
    ]);

    return response()->json(['success' => true]);
  }

  // Call #2: Generate Files
  public function generateFiles(Module $module, ModuleScaffolder $scaffolder)
  {
    $baseName = class_basename($module->model_class);
    $scaffolder->createModelFile($baseName, $module->table_name, $module);
    $scaffolder->createHandlerFile($baseName, $module->model_class);
    return response()->json(['success' => true]);
  }

  // Call #3: Create Language Labels
  public function createLabels(Module $module, ModuleScaffolder $scaffolder)
  {
    $scaffolder->createModuleLabels($module);
    return response()->json(['error' => true]);
  }

  // Call #4: Create Table 
  public function createTable(Module $module, ModuleScaffolder $scaffolder)
  {
    // If this fails, the frontend catches the error, 
    // but the fields are still "Draft," so the UI stays clean.
    $scaffolder->createTable($module->table_name, $module);

    return response()->json(['success' => true]);
  }

  // Call #5: Activate & Publish 
  public function activateFields(Module $module, ModuleScaffolder $scaffolder)
  {
    // 1. Mark the fields as active in the DB
    $scaffolder->activateFields($module);

    // 2. Final Step: The module is no longer a draft and is now live!
    $module->update([
      'is_draft' => false,
      'is_active' => true,
      'deployed_at' => now(),
    ]);

    return response()->json(['success' => true]);
  }

  // rollback, only triggered if scaffolding fails
  public function rollback(Module $module, ModuleScaffolder $scaffolder)
  {
    $scaffolder->rollback($module);
    return response()->json(['success' => true]);
  }

  /**
   * Ensures the custom module directory exists and is writable.
   */
  private function ensureDirectoryIsReady(): void
  {
    $path = app_path('Models/Modules/Custom');

    if (!File::exists($path)) {
      File::makeDirectory($path, 0755, true);
    }

    if (!is_writable($path)) {
      abort(403, "The directory {$path} is not writable. Please check server permissions.");
    }
  }
}
