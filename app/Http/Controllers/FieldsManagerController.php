<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Contracts\ModuleHandler;
use Illuminate\Http\Request;
use App\Models\Settings\SettingItem;
use App\Models\Module;
use App\Models\Field;
use App\Models\Label;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class FieldsManagerController extends Controller
{
  /**
   * Display a listing of the resource.
   */

  public function index(Request $request)
  {
    $modules = Module::query()
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
    $field_types = config("default_field_types");
    $field  = new Field();
    return Inertia::render('Settings/Fields/Create', [
      'module'     => $module,
      'item'     => $item,
      'field_types' => $field_types,
      'metadata' => $field->getEmptyMetadata()
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
    return Inertia::render('Settings/Fields/List', [
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

  public function update(Request $request, string $module, string $field_name)
  {
    $field = Field::query()
      ->where('module_id', $module)
      ->where('name', $field_name)
      ->firstOrFail();

    $data = $request->validate([
      'label' => ['required', 'string', 'min:3'],
      'readonly' => ['boolean'],
      'hidden' => ['boolean'],
      'required' => ['boolean'],
      'searchable' => ['boolean'],
      'filterable' => ['boolean'],
      'sortable' => ['boolean'],
      'default_value' => ['nullable'],
      'options' => ['nullable', 'array'],
      'min_length' => ['nullable', 'integer'],
      'max_length' => ['nullable', 'integer'],
      'regex' => ['nullable', 'string'],
    ]);

    //handle language label seperately from the rest of metadata
    if ($request->input('label') !== $field->label) {
      $key = $field->label;
      $value = $request->input('label');
      Label::updateOrCreate(
        ['key' => $key],
        [
          'value' => $value,
          'module_id' => $module
        ]

      );
    }
    $field->update($data);

    return back();
  }

  public function store(Request $request, string $module_id)
  {
    $module = Module::query()->where("id", $module_id)->first();
    $table = $module->table_name ?  $module->table_name : null;
    if (!Schema::hasTable($table)) {
      throw ValidationException::withMessages([
        'table_missing' => "System error: the module " . $module->slug . " has no SQL Database table, Please Contact your System Admin"
      ]);
    }

    $data = $request->validate([
      'label' => ['required', 'string', 'min:4'],
      'name' => ['required', 'string'],
      'key' => ['required', 'string', 'unique:fields,key,except,id'],
      'type' => ['required'],
      'dropdown_list' => ['nullable'],
      'readonly' => ['boolean'],
      'required' => ['boolean'],
      'sortable' => ['boolean'],
      'default_value' => ['nullable'],
      'min_length' => ['nullable', 'integer'],
      'max_length' => ['nullable', 'integer'],
      'regex' => ['nullable', 'string'],
    ]);

    $field_name = $data['name'];
    $label_key = "modules." . $module->slug . ".fields." . $field_name;
    $label_value = $data['label'];

    $dropdown_list = null;
    if (isset($data['dropdown_list']) && $data['type'] === "dropdown") {
      $dropdown_list = $data['dropdown_list'];
    }

    Label::updateOrCreate([
      'key' => $label_key,
      'value' => $label_value,
      'module_id' => $module_id,
      'is_custom' => true
    ]);


    Field::updateOrCreate([
      'name'  => $field_name,
      'module_id' => $module_id,
      'label' =>  $label_key,
      'key'  => $data['key'],
      'type'  => $data['type'],
      'readonly'  => $data['readonly'],
      'required'  => $data['required'],
      'sortable'  => $data['sortable'],
      'default_value'  => $data['default_value'],
      'min_length'  => $data['min_length'],
      'max_length'  => $data['max_length'],
      'regex'  => $data['regex'],
      'dropdown_list_id' => $dropdown_list,
      'is_custom' => 1
    ]);

    return redirect()
      ->route('settings.modules.fields.index', $module_id);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
