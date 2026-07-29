<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\DetectsUnusedLangKeys;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FindUnusedLangKeys extends Command
{
  use DetectsUnusedLangKeys;

  protected $signature = 'lang:unused
                            {--locale=en : Locale whose keys to check}
                            {--paths=app,resources,routes,database,config : Comma-separated list of directories to scan for usages}';

  protected $description = 'Find translation keys that are defined but never referenced in the codebase';

  public function handle()
  {
    $locale = $this->option('locale');
    $localePath = lang_path($locale);

    if (!File::exists($localePath)) {
      $this->error("Locale folder [{$locale}] does not exist.");
      return;
    }

    $this->info("Loading keys from lang/{$locale}...");
    $definedKeys = $this->loadDefinedKeys($localePath);
    $this->line('Found ' . count($definedKeys) . ' keys.');

    $paths = array_map('trim', explode(',', $this->option('paths')));
    $this->line('Scanning ' . count($this->collectFiles($paths)) . ' files for usages...');

    [$unused, $dynamicPrefixes] = $this->findUnusedLangKeys($locale, $paths);

    if (empty($unused)) {
      $this->info("\n✓ No unused keys found.");
      return;
    }

    $this->warn("\nUnused keys (" . count($unused) . "):");
    foreach ($unused as $key) {
      $this->line("  - {$key}");
    }

    if (!empty($dynamicPrefixes)) {
      $this->line("\nNote: some keys are referenced dynamically (e.g. interpolated variables).");
      $this->line('Keys under these prefixes were excluded even though they cannot be fully verified:');
      foreach (array_keys($dynamicPrefixes) as $prefix) {
        $this->line("  - {$prefix}*");
      }
    }
  }
}
