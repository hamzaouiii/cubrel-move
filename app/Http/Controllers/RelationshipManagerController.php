<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\Relationship;
use App\Models\DropdownList;
use Illuminate\Validation\ValidationException;

class RelationshipManagerController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request, string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)->first();
    $relationships = $module->relationships();
    return Inertia::render('Settings/Relationships/List', [
      'module' => $module,
      'relationships' => $relationships
    ]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)->first();
    $types = config("default_relationship_types");
    $relationship  = new Relationship();

    // we are preventing self-referncing relationships for now, so the list of possible modules must exclude the current module, 
    // therefore we cannot use the dropdown list module_list because this has to be dynamic. Also we need to enforce this on the Service and model level.
    $moduleList = [
      'values' => Module::query()
        ->where('is_active', 1)
        ->where('id', '!=', $module_id)
        ->orderBy('slug')
        ->get()
        ->map(fn($module) => [
          'label' => "modules.{$module->slug}.label",
          'value' => $module->slug,
        ])
        ->values()
        ->toArray()
    ];
    $typeList = DropdownList::query()->where('key', 'relationship_type_list')->firstOrFail();
    return Inertia::render('Settings/Relationships/Create', [
      'module'     => $module,
      'types' => $types,
      'metadata' => $relationship->getEmptyMetadata(),
      'moduleList'  => $moduleList,
      'typeList'  => $typeList,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request, Module $module)
  {
    $validated = $request->validate([
      'name' => ['required', 'string', 'max:255', 'unique:relationships,name'],
      'label' => ['required', 'string', 'max:255'],
      'right_module' => ['required', 'string', 'exists:modules,slug'],
      'type' => ['required', 'in:one-to-one,one-to-many,many-to-one,many-to-many'],
    ]);

    $leftModule = $module;
    $rightModule = Module::query()
      ->where('slug', $validated['right_module'])
      ->firstOrFail();

    // Prevent self-referencing relationships
    if ($leftModule->slug === $rightModule->slug) {
      return back()->withErrors([
        'right_module' => __('relationships.errors.self_reference_not_allowed')
      ]);
    }

    $type = $validated['type'];

    // 'many-to-one' is just 'one-to-many' reveresed. It it is the same relationship type in the service. 
    // we swap the left and right modules here and switch the type the one-to-many since it is the type the service can resolve 
    // this means many-to-one only really exists in this controller in the whole code base
    if ($type === 'many-to-one') {
      [$leftModule, $rightModule] = [$rightModule, $leftModule];
      $type = 'one-to-many';
    }

    //Prevent identical relationship duplicates

    $duplicate = Relationship::query()
      ->where('left_module', $leftModule->slug)
      ->where('right_module', $rightModule->slug)
      ->where('type', $type)
      ->exists();

    if ($duplicate) {
      return back()->withErrors([
        'right_module' => __('relationships.errors.duplicate_relationship')
      ]);
    }

    //Generate join table name
    $joinTable = 'relationship_links';

    Relationship::create([
      'name' => $validated['name'],
      'label' => $validated['label'],
      'left_module' => $leftModule->slug,
      'right_module' => $rightModule->slug,
      'type' => $type,
      'join_table' => $joinTable,
      'is_system' => 0,
    ]);

    return redirect()
      ->route('settings.relationships.index', $module)
      ->with('success', __('relationships.created'));
  }


  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $module_id, string $relationship_id)
  {
    $relationship = Relationship::findOrFail($relationship_id);
    if ($relationship->is_system) {
      throw ValidationException::withMessages([
        'rel' => __('relationships.system_delete_forbidden')
      ]);
    }
    $relationship->cleanupRelationshipPanels($module_id);
    $relationship->delete();

    return back()->with('success', __('relationships.deleted'));
  }
}
