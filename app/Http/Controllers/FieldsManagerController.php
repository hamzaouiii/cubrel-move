<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use App\Models\Settings\SettingItem;
use App\Models\Module;

class FieldsManagerController extends Controller
{
  /**
   * Display a listing of the resource.
   */

  public function index(Request $request)
  {
    $modules = Module::query()
      ->with([
        'layouts' => function ($q) {
          $q->orderBy('type')->orderBy('name');
        },
      ])
      ->orderBy('id')
      ->get();
    $item = SettingItem::where('path', 'like', '%' . $request->path())->first();
    return Inertia::render('Settings/Fields/Record', [
      'item'     => $item,
      'setting_modules' => $modules
    ]);
  }


  /**
   * Show the form for creating a new resource.
   */
  public function create(Request $request, string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)
      ->firstOrFail();

    $routeUri = $request->route()->uri();
    $routeUri = explode("/", $routeUri);
    $ptt = "/" . $routeUri[0] . "/" . $routeUri[1];
    $item = SettingItem::where('path', 'like', '%' . $ptt)->first();

    return Inertia::render('Settings/Fields/Create', [
      'module'     => $module,
      'item'     => $item
    ]);
  }


  /**
   * Display the specified resource.
   */
  public function show(Request $request, string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)
      ->firstOrFail();

    $routeUri = $request->route()->uri();
    $routeUri = explode("/", $routeUri);
    $ptt = "/" . $routeUri[0] . "/" . $routeUri[1];
    $item = SettingItem::where('path', 'like', '%' . $ptt)->first();

    return Inertia::render('Settings/Fields/Record', [
      'module' => $module,
      'item'   => $item,
      'fields' => $module->fields
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Request $request, string $module, string $field)
  {
    $module = Module::query()
      ->where('id', $module)
      ->firstOrFail();

    $routeUri = $request->route()->uri();
    $routeUri = explode("/", $routeUri);
    $ptt = "/" . $routeUri[0] . "/" . $routeUri[1];
    $item = SettingItem::where('path', 'like', '%' . $ptt)->first();
    $field_types = config("default_field_types");
    return Inertia::render('Settings/Fields/Edit', [
      'module'     => $module,
      'item'     => $item,
      'metadata' => $module->getFieldMetadata($field),
      'field_types' => $field_types
    ]);
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
