<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Handlers\Modules\UserModuleHandler;
use Illuminate\Http\Request;
use App\Models\Module;
use Inertia\Inertia;

class UserController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    $moduleModel = Module::query()
      ->where('slug', 'users')
      ->where('is_active', true)
      ->firstOrFail();

    $handler = new UserModuleHandler();
    $props = $handler->getListData($moduleModel);

    $listLayout = config("module_layouts.users.list");
    $recorddropdownLists = $moduleModel->dropdownLists;
    $fields = $moduleModel->allFields();

    return Inertia::render('Users/List', array_merge([
      'module'     => $moduleModel,
      'title'      => $moduleModel->name,
      'listLayout' => $listLayout,
      'fields'     => $fields,
      'filters'    => request()->only(['search', 'perPage']),
      'dropdownLists' => $recorddropdownLists,

    ], $props));
  }

  /**
   * Show the form for creating a new resource.
   */

  public function create()
  {
    $module = 'users';
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $recordLayout = config("module_layouts.users.record");

    $fields        = $moduleModel->allFields();
    $recorddropdownLists = $moduleModel->dropdownLists;

    return Inertia::render('Modules/Create', array_merge([
      'module'        => $moduleModel,
      'title'         => $moduleModel->name,
      'recordLayout'  => $recordLayout,
      'dropdownLists' => $recorddropdownLists,
      'fields'        => $fields,
    ]));
  }

  /**
   * Display the specified resource.
   */
  public function show(string $user)
  {
    $module = 'users';
    $moduleModel = Module::query()
      ->where('slug', $module)
      ->where('is_active', true)
      ->firstOrFail();

    $props = [];

    $handler = new UserModuleHandler();

    $props = $handler->getRecordData($module, $user, request()->all());

    // $recordLayout  = $moduleModel->recordLayout();
    $recordLayout = config("module_layouts.users.record");

    $relatedLayout = $moduleModel->relatedLayout();
    $fields        = $moduleModel->allFields();

    return Inertia::render('Users/Record', array_merge([
      'module'         => $moduleModel,
      'title'          => $moduleModel->name,
      'overviewLayout' => $recordLayout,
      'relatedLayout'  => $relatedLayout,
      'fields'         => $fields,
    ], $props));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(User $user)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, User $user)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(User $user)
  {
    //
  }
}
