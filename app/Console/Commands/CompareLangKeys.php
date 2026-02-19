<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CompareLangKeys extends Command
{
  protected $signature = 'lang:compare 
                            {--from=en : Source locale}
                            {--to=de : Target locale}';

  protected $description = 'Compare translation keys between two locales';

  public function handle()
  {
    $from = $this->option('from');
    $to   = $this->option('to');

    $fromPath = lang_path($from);
    $toPath   = lang_path($to);

    if (!File::exists($fromPath) || !File::exists($toPath)) {
      $this->error('One of the locale folders does not exist.');
      return;
    }

    $fromFiles = collect(File::files($fromPath))->keyBy->getFilename();
    $toFiles   = collect(File::files($toPath))->keyBy->getFilename();

    $allFiles = $fromFiles->keys()->merge($toFiles->keys())->unique();

    foreach ($allFiles as $file) {

      $this->line("\n<info>Comparing file: {$file}</info>");

      $fromKeys = [];
      $toKeys   = [];

      if ($fromFiles->has($file)) {
        $fromKeys = $this->flatten(require $fromFiles[$file]->getPathname());
      }

      if ($toFiles->has($file)) {
        $toKeys = $this->flatten(require $toFiles[$file]->getPathname());
      }

      $missingInTo   = array_diff(array_keys($fromKeys), array_keys($toKeys));
      $missingInFrom = array_diff(array_keys($toKeys), array_keys($fromKeys));

      if (empty($missingInTo) && empty($missingInFrom)) {
        $this->info('✓ No missing keys');
        continue;
      }

      if (!empty($missingInTo)) {
        $this->warn("Missing in {$to}:");
        foreach ($missingInTo as $key) {
          $this->line("  - {$key}");
        }
      }

      if (!empty($missingInFrom)) {
        $this->warn("Missing in {$from}:");
        foreach ($missingInFrom as $key) {
          $this->line("  - {$key}");
        }
      }
    }

    $this->line("\nDone.");
  }

  private function flatten(array $array, string $prefix = ''): array
  {
    $result = [];

    foreach ($array as $key => $value) {
      $newKey = $prefix === '' ? $key : $prefix . '.' . $key;

      if (is_array($value)) {
        $result += $this->flatten($value, $newKey);
      } else {
        $result[$newKey] = $value;
      }
    }

    return $result;
  }
}
