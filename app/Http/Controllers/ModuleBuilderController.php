<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\ModuleScaffolder;
use App\Services\Relationships\RelationshipService;
use Illuminate\Support\Str;
use App\Support\RandomColorGenerator;
use App\Support\RandomIconGenerator;
use Illuminate\Support\Facades\DB;
use App\Models\DropdownList;
use App\Models\Field;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ModuleBuilderController extends Controller
{
  /**
   * Instantiates a new draft module and redirects to the builder interface.
   */
  public function create()
  {
    $user_id = Auth::id();
    $category_list = DropdownList::get('module_category_list');
    $module = $this->getOrCreateDraftModule($user_id);
    $field = new Field();
    $field_modules = Module::select('slug', 'icon', 'color')
      ->where('is_active', true)
      ->where('is_relatable', true)
      ->get();
    return Inertia::render('Settings/Modules/Create', [
      'settingModule' => $module,
      'categoryList'  => $category_list,
      'fields'        => $module->builderFields(),
      'field_types' => config("default_field_types"),
      'metadata' => $field->getEmptyMetadata(),
      'moduleOptions' => $this->lineItemSourceOptions($module),
      'fieldModules' => $field_modules,

    ]);
  }

  /**
   * Modules eligible to be picked as a "line item source" — i.e. flagged
   * is_product_like, excluding the module currently being built/edited and
   * the internal line_items module itself.
   */
  protected function lineItemSourceOptions(Module $module): array
  {
    return Module::query()
      ->where('is_product_like', true)
      ->where('is_active', true)
      ->where('id', '!=', $module->id)
      ->orderBy('name')
      ->get(['slug', 'label'])
      ->map(fn ($m) => ['value' => $m->slug, 'label' => $m->label])
      ->values()
      ->all();
  }

  public function update(Request $request, Module $module)
  {
    $validated = $request->validate([
      'display_label'   => ['required', 'string', 'max:255'],
      'single_label'   => ['required', 'string', 'max:255'],
      'slug' => [
        'required',
        'string',
        'max:255',
        'alpha_dash',
        Rule::notIn(config('reserved_keywords.slugs')),
        'unique:modules,slug,' . $module->id,
      ],
      'icon'            => ['nullable', 'string', 'max:255'],
      'color'           => ['nullable', 'string', 'max:255'],
      'description'     => ['nullable', 'string'],
      'category'     => ['required', 'string'],
      'show_in_sidebar' => ['boolean'],
      'has_line_items' => ['required', 'boolean'],
      'is_product_like' => ['boolean'],
      'is_relatable' => ['boolean'],
      'is_activity' => ['boolean'],
      'has_activity' => ['boolean'],
      'line_item_source_module' => [
        'nullable',
        'string',
        'exists:modules,slug',
        Rule::notIn([$module->slug, 'line_items']),
        Rule::requiredIf($request->boolean('has_line_items')),
      ],
    ]);
    $baseName = $validated['display_label'];
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
      'label'           => $validated['display_label'], // to be properly handeled after deploying the module
      'single_label'    => $validated['single_label'], // same
      'handler_class'   => "App\\Handlers\\Modules\\Custom\\" . $baseName . "ModuleHandler",
      'model_class'     => "App\\Models\\Modules\\Custom\\" . $baseName,
      'table_name'      => Str::snake($validated['slug']) . "_cstm",
      'path'            => '/' . $validated['slug'],
      'has_line_items' => $validated['has_line_items'],
      'has_owner' => true,
      'show_in_module_manager' => true,
      'is_product_like' => $validated['is_product_like'] ?? false,
      'is_relatable' => $validated['is_relatable'] ?? true,
      'is_activity' => $validated['is_activity'] ?? false,
      'has_activity' => $validated['has_activity'] ?? false,
      'line_item_source_module' => $validated['has_line_items']
        ? $validated['line_item_source_module']
        : null,

    ]);

    RelationshipService::syncActivityRelationships($module);

    return back();
  }
  /**
   * Validates final user input, converts the draft to active, and scaffolds the table.
   */
  public function deploy(Request $request, Module $module)
  {
    $validated = $request->validate([
      'display_label'   => ['required', 'string', 'max:255'],
      'single_label'   => ['required', 'string', 'max:255'],
      'slug' => [
        'required',
        'string',
        'max:255',
        'alpha_dash',
        Rule::notIn(config('reserved_keywords.slugs')),
        'unique:modules,slug,' . $module->id,
      ],
      'icon'            => ['nullable', 'string', 'max:255'],
      'color'           => ['nullable', 'string', 'max:255'],
      'description'     => ['nullable', 'string'],
      'category'     => ['required', 'string'],
      'show_in_sidebar' => ['boolean'],
    ]);

    $baseName = Str::studly($validated['slug']);
    $DEFAULT_ICON          = 'fa-solid fa-bahai';
    $DEFAULT_SORT_ORDER    = (Module::max('sort_order') ?? 0) + 1;

    $module->update([
      'name'        => $validated['display_label'],
      'slug'            => $validated['slug'],
      'icon'            => $validated['icon'] ??  $DEFAULT_ICON,
      'color'           => $validated['color'] ?? '#000000',
      'description'     => $validated['description'] ?? '',
      'show_in_sidebar' => $validated['show_in_sidebar'] ?? true,
      'show_in_module_manager' => true,
      'category'        => $validated['category'],
      'sort_order'      => $DEFAULT_SORT_ORDER,
      'is_draft'        => false,
      'is_active'       => true,
      'handler_class'   => "App\\Handlers\\Modules\\Custom\\" . $baseName . "ModuleHandler",
      'model_class'     => "App\\Models\\Modules\\Custom\\" . $baseName,
      'table_name'      =>  "cstm_" . Str::snake($validated['slug']),
      'path'            => '/' . $validated['slug'],
    ]);

    // magic
    app(ModuleScaffolder::class)->scaffold($module, $validated['display_label'], $validated['single_label']);

    return back();
  }

  public function saveDraftField(Request $request, Module $module)
  {
       $nameRules = ['required', 'string', Rule::notIn(array_keys(config('default_fields')))];

    if ($module->has_line_items) {
        $nameRules[] = Rule::notIn(array_keys(config('default_line_item_fields')));
    }
    $data = $request->validate([
      'id' => ['nullable', 'exists:fields,id'],
      'label' => ['required', 'string', 'min:4'],
      'name' =>  $nameRules,
      'key' => [
        'required',
        'string',
        Rule::unique('fields', 'key')->ignore($request->id),
      ],
      'type' => ['required'],
      'dropdown_list' => ['nullable', 'required_if:type,select', 'exists:dropdown_lists,id'],
      'readonly' => ['boolean'],
      'required' => ['boolean'],
      'sortable' => ['boolean'],
      'default_value' => ['nullable'],
      'min_length' => ['nullable', 'integer'],
      'max_length' => ['nullable', 'integer'],
      'regex' => ['nullable', 'string'],
      'related_module' => ['nullable', 'string'],
    ]);

    $data = array_merge([
      'default_value' => null,
      'min_length' => null,
      'max_length' => null,
      'regex' => null,
      'related_module' => null,
      'readonly' => false,
      'required' => false,
      'sortable' => false,
    ], $data);

    $dropdown_list = null;
    if (!empty($data['dropdown_list']) && in_array($data['type'], ['select', 'status'], true)) {
      $dropdown_list = $data['dropdown_list'];
    }

    $field = $module->fields()->find($data['id'] ?? null);

    if ($field) {
      $field->update([
        ...$data,
        'dropdown_list_id' => $dropdown_list,
      ]);
    } else {
      $module->fields()->create([
        ...$data,
        'dropdown_list_id' => $dropdown_list,
        'is_draft' => 1,
      ]);
    }

    return back()->with('success');
  }

  /**
   * Explicitly abandons a draft module — generated files/table/labels are
   * cleaned up, its fields and the module row itself are deleted.
   */
  public function discard(Module $module)
  {
    if (! $module->is_draft) {
      return response()->json(['message' => 'Module is not a draft.'], 422);
    }

    $user = Auth::user();
    $isOwner = $module->locked_by === $user->id;
    $isAdmin = $user->is_admin || $user->is_root;

    if (! $isOwner && ! $isAdmin) {
      return response()->json(['message' => 'You do not have permission to discard this draft.'], 403);
    }

    app(ModuleScaffolder::class)->discardDraft($module);

    return redirect()->route('settings.index')->with('success', 'Draft discarded successfully.');
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
          'show_in_module_manager' => false,

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

  public function deleteDraftField(Request $request, Module $module, Field $field)
  {
    try {

      if (!$field->is_draft) {
        return response()->json([
          'message' => 'Field is not a draft and cannot be deleted.'
        ], 422);
      }

      $field->delete();

      return response()->json([
        'message' => 'Field deleted successfully.'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'message' => 'Something went wrong!',
        'error' => $e->getMessage(),
      ], 500);
    }
  }
}
