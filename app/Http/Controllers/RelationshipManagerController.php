<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Module;

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
  public function create()
  {
    //
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
  public function show(string $id)
  {
    //
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
