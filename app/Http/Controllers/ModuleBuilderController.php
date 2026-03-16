<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\ModuleScaffolder;
use Illuminate\Support\Str;
use App\Support\RandomColorGenerator;
use App\Support\RandomIconGenerator;
use Illuminate\Support\Facades\DB;
use App\Models\DropdownList;

class ModuleBuilderController extends Controller
{
  /**
   * Instantiates a new draft module and redirects to the builder interface.
   */
  public function create()
  {
    $category_list = DropdownList::get('module_category_list');
    $module = $this->getOrCreateDraftModule(auth()->id());
    return Inertia::render('Settings/Modules/Create', [
      'settingModule' => $module,
      'categoryList'  => $category_list
    ]);
  }

  /**
   * Shows the Builder view.
   */
  public function show(Module $module)
  {
    // Prevent active modules from being loaded in the draft builder
    if (!$module->is_draft) {
      return redirect()->route('settings.modules.show', $module->id);
    }

    return Inertia::render('Settings/Modules/Builder', [
      'settingModule' => $module
    ]);
  }

  public function update(Request $request, Module $module)
  {
    $validated = $request->validate([
      'display_label'   => ['required', 'string', 'max:255'],
      'single_label'   => ['required', 'string', 'max:255'],
      'slug'            => ['required', 'string', 'max:255', 'alpha_dash', 'unique:modules,slug,' . $module->id],
      'icon'            => ['nullable', 'string', 'max:255'],
      'color'           => ['nullable', 'string', 'max:255'],
      'description'     => ['nullable', 'string'],
      'category'     => ['required', 'string'],
      'show_in_sidebar' => ['boolean'],
    ]);
    $baseName = Str::studly($validated['slug']);
    $module->update([
      'name'            => $baseName,
      'slug'            => $validated['slug'],
      'icon'            => $validated['icon'] ?? 'fa-solid fa-cube',
      'color'           => $validated['color'] ?? '#000000',
      'description'     => $validated['description'] ?? '',
      'show_in_sidebar' => $validated['show_in_sidebar'] ?? true,
      'category'        => $validated['category'],
      'is_draft'        => true,
      'is_active'       => false,
      'label'           => $validated['display_label'],
      'single_label'    => $validated['single_label'],
      'handler_class'   => "App\\Handlers\\Modules\\Custom\\" . $baseName . "ModuleHandler",
      'model_class'     => "App\\Models\\Modules\\Custom\\" . $baseName,
      'table_name'      => Str::snake($validated['slug']) . "_cstm",
      'path'            => '/' . $validated['slug'],
    ]);


    return back();
  }
  /**
   * Validates final user input, converts the draft to active, and scaffolds the table.
   */
  public function publish(Request $request, Module $module)
  {
    $validated = $request->validate([
      'display_label'   => ['required', 'string', 'max:255'],
      'slug'            => ['required', 'string', 'max:255', 'alpha_dash', 'unique:modules,slug,' . $module->id],
      'icon'            => ['nullable', 'string', 'max:255'],
      'color'           => ['nullable', 'string', 'max:255'],
      'description'     => ['nullable', 'string'],
      'show_in_sidebar' => ['boolean'],
    ]);

    $baseName = Str::studly($validated['slug']);

    // Finalize the module properties
    $module->update([
      'name'            => $validated['display_label'],
      'slug'            => $validated['slug'],
      'icon'            => $validated['icon'] ?? 'fa-solid fa-cube',
      'color'           => $validated['color'] ?? '#000000',
      'description'     => $validated['description'] ?? '',
      'show_in_sidebar' => $validated['show_in_sidebar'] ?? true,
      'is_draft'        => false,
      'is_active'       => true,
      'label'           => 'custom/modules/' . $validated['slug'] . '.label',
      'handler_class'   => "App\\Handlers\\Modules\\Custom\\" . $baseName . "ModuleHandler",
      'model_class'     => "App\\Models\\Modules\\Custom\\" . $baseName,
      'table_name'      => Str::snake($validated['slug']) . "_cstm",
      'path'            => '/' . $validated['slug'],
    ]);

    // NOW scaffold the tables/files since the user has had time to define Fields and Layouts
    app(ModuleScaffolder::class)->scaffold($module, $validated['display_label']);

    return redirect()
      ->route('modules.show', $module->id)
      ->with('success', __('settings.module_publish_success'));
  }


  public function getOrCreateDraftModule(string $userId): Module
  {
    return DB::transaction(function () use ($userId) {

      // 1. If the user already has a draft, return it
      $module = Module::where('is_draft', true)
        ->where('locked_by', $userId)
        ->lockForUpdate()
        ->first();

      if ($module) {
        $module->update([
          'locked_until' => now()->addHours(2),
        ]);

        return $module;
      }

      // 2. Find an available draft
      $module = Module::where('is_draft', true)
        ->where(function ($q) {
          $q->whereNull('locked_until')
            ->orWhere('locked_until', '<', now());
        })
        ->lockForUpdate()
        ->first();

      // 3. If none exists create one
      if (!$module) {

        $draftId = uniqid('draft_');

        $module = Module::create([
          'name' => 'New Module',
          'slug' => $draftId,
          'is_draft' => true,
          'is_active' => false,
          'is_custom' => true,
          'icon' => RandomIconGenerator::random(),
          'color' => RandomColorGenerator::random(),
          'sort_order' => (Module::max('sort_order') ?? 0) + 1,
          'table_name' => 'draft_cstm',
          'path' => '/' . $draftId,
          'show_in_sidebar' => false,
          'locked_by' => $userId,
          'locked_until' => now()->addHours(2),
        ]);
      }

      // 4. Lock the draft for this user
      $module->update([
        'locked_by' => $userId,
        'locked_until' => now()->addHours(2),
      ]);

      return  Module::where('id', $module->id)
        ->lockForUpdate()
        ->first();
    });
  }
}
