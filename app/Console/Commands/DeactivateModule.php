<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;

class DeactivateModule extends Command
{
  protected $signature = 'modules:deactivate {slug : The slug of the module to deactivate}';
  protected $description = 'Deactivates a module by slug (blocks CRUD/list/search/dashboards, hides its relationships/related panels on other modules, and hides it from the sidebar)';

  public function handle()
  {
    $slug = $this->argument('slug');

    $module = Module::where('slug', $slug)->first();

    if (! $module) {
      $this->error("Module not found: {$slug}");
      return self::FAILURE;
    }

    if (! $module->is_active && ! $module->show_in_sidebar) {
      $this->info("Module '{$slug}' is already deactivated.");
      return self::SUCCESS;
    }

    $module->is_active = false;
    $module->show_in_sidebar = false;
    $module->save();

    $this->info("Module '{$slug}' deactivated.");

    return self::SUCCESS;
  }
}
