<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;

class ModulesTableSeeder extends Seeder
{
  public function run()
  {

    foreach (config('modules') as $module) {
      // Seeding runs outside any authenticated request (artisan/CLI), so
      // AdminOnlyModuleScope would hide the existing 'settings'/'users' rows
      // from this lookup and firstOrCreate would try (and fail) to re-insert them.
      Module::withoutGlobalScope(AdminOnlyModuleScope::class)
        ->firstOrCreate(['slug' => $module['slug']], $module);
    }
  }
}
