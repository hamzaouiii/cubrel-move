<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Field;
use App\Models\Module;
use App\Models\DropDownList;
use Illuminate\Support\Str;
use App\Scopes\AdminOnlyModuleScope;

class StockFieldsSeeder extends Seeder
{
  public function run(): void
  {
    // seed default fields
    $defaults = config("default_fields", []);
    foreach ($defaults as $fieldKey => $default) {
      Field::updateOrCreate(
        [
          'name'      => $fieldKey,
          'is_global' => true
        ],
        array_merge($default, [
          'module_id' => null,
          'key'               => "defaults_{$fieldKey}",
          'label'             => "modules.defaults.{$fieldKey}",
          'id'                =>  Str::uuid(),
          'is_custom'         => false,
          'is_active'         => true,
          'sortable'         => true,
        ])
      );
    }
    foreach (Module::withoutGlobalScope(AdminOnlyModuleScope::class)->get() as $module) {
      $definitions = config("stock_fields.{$module->slug}", []);

      foreach ($definitions as $fieldKey => $definition) {

        $dropdownListId = null;

        if (($definition['type'] ?? null) === 'select' || ($definition['type'] ?? null) === 'status') {

          // Convention: module_field_list unless it is currency which is global
          // maybe not a good idea ??
          $dropdownKey = $fieldKey === 'currency' ? "{$fieldKey}_list" : "{$module->slug}_{$fieldKey}_list";

          $dropdown = DropDownList::where('key', $dropdownKey)->first();

          if ($dropdown) {
            $dropdownListId = $dropdown->id;
          }
        }

        Field::updateOrCreate(
          [
            'module_id' => $module->id,
            'name'      => $fieldKey,
          ],
          array_merge($definition, [
            'key'               => "{$module->slug}_{$fieldKey}",
            'label'             => "modules.{$module->slug}.fields.{$fieldKey}",
            'id'                => Field::where('module_id', $module->id)
              ->where('name', $fieldKey)
              ->value('id') ?? (string) Str::uuid(),
            'dropdown_list_id'  => $dropdownListId,
            'is_custom'         => false,
            'is_active'         => true,
            'sortable'         => true,
          ])
        );
      }
    }
  }
}
