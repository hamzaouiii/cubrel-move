<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Field;
use App\Models\Module;
use Illuminate\Support\Str;

class FieldsUpdaterSeeder extends Seeder
{
  public function run(): void
  {
    $fields = ['created_at', 'updated_at'];
    foreach (Module::all() as $module) {
      foreach ($fields as $f)
        Field::firstOrCreate(
          [
            'module_id' => $module->id,
            'name'      => $f,
            'key'       => "{$module->slug}_{$f}",
            'label'     => "modules.defaults.{$f}",
            'id'        => (string) Str::uuid(),
            'is_custom' => false,
            'is_active' => true,
            'type' => 'datetime',
            'required' => true,
            'readonly' => true,
          ]
        );
    }
  }
}
