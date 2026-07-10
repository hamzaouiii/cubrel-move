<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Field;
use App\Models\Label;
use App\Models\Layout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FieldsManagerController extends Controller
{
  /**
   * Show the form for creating a new resource.
   */
  public function create(string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)
      ->firstOrFail();

    $field_types = config("icon_default_field_types");
    $field_modules = Module::select('slug', 'icon', 'color')
    ->where('is_active', true)
    ->where('is_relatable', true)
    ->get();
    $field  = new Field();
    return Inertia::render('Settings/Fields/Create', [
      'module'     => $module,
      'fieldTypes' => $field_types,
      'fieldModules' => $field_modules,
      'metadata' => $field->getEmptyMetadata()
    ]);
  }


  /**
   * Display the specified resource.
   */
  public function show(string $module_id)
  {
    $module = Module::query()
      ->where('id', $module_id)
      ->firstOrFail();

    $fields = $module->allEditableFields()->map(function (Field $field) use ($module) {
      $field->records_using = $field->is_custom
        ? $this->countRecordsUsingField($module, $field->name)
        : null;

      return $field;
    });

    return Inertia::render('Settings/Fields/List', [
      'module' => $module,
      'fields' => $fields
    ]);
  }

  /**
   * count how my records have data for field in module table
   * @param Module $module
   * @param string $fieldName
   * @return int
   */
  private function countRecordsUsingField(Module $module, string $fieldName): int
  {
    if (! $module->table_name || ! Schema::hasTable($module->table_name)) {
      return 0;
    }

    return DB::table($module->table_name)
      ->whereNotNull("custom_fields->{$fieldName}")
      ->count();
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
    $field_types = config("default_field_types");
    return Inertia::render('Settings/Fields/Edit', [
      'module'     => $module,
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
      'related_module' => ['nullable', 'string'],
      'dropdown_list' => ['nullable', 'exists:dropdown_lists,id'],
    ]);

    if (array_key_exists('dropdown_list', $data)) {
      $data['dropdown_list_id'] = $data['dropdown_list'];
      unset($data['dropdown_list']);
    }

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

    $field_name = $data['name'];
    $label_key = "modules." . $module->slug . ".fields." . $field_name;
    $label_value = $data['label'];

    $dropdown_list = null;
    if (isset($data['dropdown_list']) && in_array($data['type'], ['select', 'status'], true)) {
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
      'related_module'  => $data['related_module'],
      'dropdown_list_id' => $dropdown_list,
      'is_custom' => 1
    ]);

    return redirect()
      ->route('settings.modules.fields.index', $module_id);
  }

  /**
   * delete field
   * only custom fields can be deleted
   * @param Request $request
   * @param string $module_id
   * @param string $field_name
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Request $request, string $module_id, string $field_name)
  {
    $module = Module::query()->where('id', $module_id)->firstOrFail();

    $field = Field::query()
      ->where('module_id', $module_id)
      ->where('name', $field_name)
      ->firstOrFail();

    if (! $field->is_custom) {
      throw ValidationException::withMessages([
        'field' => __('fields.delete_forbidden_stock'),
      ]);
    }

    $field->delete();

    $this->removeFieldFromLayouts($module, $field_name);

    return back()->with('success', __('fields.field_delete_success'));
  }

  /**
   * delete field definition from layouts that already contain it
   * @param Module $module
   * @param string $fieldName
   * @return void
   */
  private function removeFieldFromLayouts(Module $module, string $fieldName): void
  {
    Layout::where('module_id', $module->id)
      ->whereIn('type', ['list', 'linkingPanel', 'record'])
      ->get()
      ->each(function (Layout $layout) use ($fieldName) {
        $definition = $layout->definition ?? [];

        if ($layout->type === 'record') {
          foreach ($definition['sections'] ?? [] as $i => $section) {
            $definition['sections'][$i]['layout'] = array_values(
              array_filter($section['layout'] ?? [], fn ($f) => ($f['name'] ?? null) !== $fieldName)
            );
          }
        } else {
          $definition['columns'] = array_values(
            array_filter($definition['columns'] ?? [], fn ($c) => ($c['name'] ?? null) !== $fieldName)
          );
        }

        $layout->update(['definition' => $definition]);
      });
  }
}
