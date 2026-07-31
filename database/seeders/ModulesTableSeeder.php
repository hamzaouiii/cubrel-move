<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;

class ModulesTableSeeder extends Seeder
{
  public function run()
  {

    foreach (config('modules') as $module) {
      Module::firstOrCreate(['slug' => $module['slug']], $module);
    }
  }
}
