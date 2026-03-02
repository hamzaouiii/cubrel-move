<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Field;
use App\Models\Module;
use App\Models\DropDownList;
use Illuminate\Support\Str;

class StockFieldsSeeder extends Seeder
{
  public function run(): void
  {
    foreach (Module::all() as $module) {

      $definitions = config("stock_fields.{$module->slug}", []);

      foreach ($definitions as $fieldKey => $definition) {

        $dropdownListId = null;

        if (($definition['type'] ?? null) === 'dropdown') {

          // Convention: module_field_list unless it is currency which is global
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
          ])
        );
      }
    }
  }
}
