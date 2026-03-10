<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Module;
use App\Models\Relationship;
use App\Models\DropdownList;

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
    $moduleList = DropdownList::query()->where('key', 'module_list')->firstOrFail();
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
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $module_id, string $relationship_name)
  {
    $module = Module::query()
      ->where('id', $module_id)->first();
    $relationship = Relationship::query()->where('name', $relationship_name)->firstOrFail();
    return Inertia::render('Settings/Relationships/Edit', [
      'module' => $module,
      'relationship' => $relationship
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
