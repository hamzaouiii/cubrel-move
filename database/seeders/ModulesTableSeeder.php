<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Module;

class ModulesTableSeeder extends Seeder
{
  public function run()
  {
    DB::table('modules')->delete();

    foreach (config('modules') as $module) {
      Module::create($module);
    }
  }
}
