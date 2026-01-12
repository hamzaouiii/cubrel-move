<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Field;
use App\Models\Module;
use Illuminate\Support\Str;

class StockFieldsSeeder extends Seeder
{
  public function run(): void
  {
    foreach (Module::all() as $module) {
      $definitions = config("stock_fields.{$module->slug}", []);

      foreach ($definitions as $fieldKey => $definition) {
        Field::firstOrCreate(
          [
            'module_id' => $module->id,
            'name'      => $fieldKey,
            'key'       => "{$module->slug}_{$fieldKey}",
            'label'     => "modules.{$module->slug}.fields.{$fieldKey}"
          ],
          array_merge($definition, [
            'id'        => (string) Str::uuid(),
            'is_custom' => false,
            'is_active' => true,
          ])
        );
      }
    }
  }
}
