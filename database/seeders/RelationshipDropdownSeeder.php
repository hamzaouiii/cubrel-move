<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DropdownList;
use App\Models\Module;

class RelationshipDropdownSeeder extends Seeder
{
  public function run(): void
  {
    /*
        |--------------------------------------------------------------------------
        | Module List
        |--------------------------------------------------------------------------
        */

    $modules = Module::query()
      ->where('is_active', 1)
      ->orderBy('slug')
      ->get()
      ->map(fn($module) => [
        'label' => "modules.{$module->slug}.label",
        'value' => $module->slug,
      ])
      ->values()
      ->toArray();

    DropdownList::updateOrCreate(
      ['key' => 'module_list'],
      [
        'values' => $modules,
        'is_global' => 0,
      ]
    );
  }
}
