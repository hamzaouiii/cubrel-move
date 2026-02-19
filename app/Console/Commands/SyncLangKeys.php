<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncLangKeys extends Command
{
  protected $signature = 'lang:sync 
                            {--from=en}
                            {--to=de}
                            {--placeholder=true}';

  protected $description = 'Sync missing translation keys';

  public function handle()
  {
    $from = $this->option('from');
    $to   = $this->option('to');
    $usePlaceholder = $this->option('placeholder') === 'true';

    $fromPath = lang_path($from);
    $toPath   = lang_path($to);

    $fromFiles = collect(File::files($fromPath))->keyBy->getFilename();

    foreach ($fromFiles as $filename => $file) {

      $sourceArray = require $file->getPathname();

      $targetFilePath = $toPath . '/' . $filename;

      $targetArray = File::exists($targetFilePath)
        ? require $targetFilePath
        : [];

      $updated = $this->mergeRecursive(
        $sourceArray,
        $targetArray,
        $usePlaceholder
      );

      if ($updated['changed']) {
        File::put(
          $targetFilePath,
          "<?php\n\nreturn " . var_export($updated['data'], true) . ";\n"
        );

        $this->info("Synced: {$filename}");
      }
    }

    $this->info("\nDone.");
  }

  private function mergeRecursive($source, $target, $placeholder)
  {
    $changed = false;

    foreach ($source as $key => $value) {

      if (!array_key_exists($key, $target)) {
        $target[$key] = is_array($value)
          ? $value
          : ($placeholder ? "[MISSING] {$value}" : $value);

        $changed = true;
        continue;
      }

      if (is_array($value)) {
        $result = $this->mergeRecursive(
          $value,
          $target[$key] ?? [],
          $placeholder
        );

        $target[$key] = $result['data'];

        if ($result['changed']) {
          $changed = true;
        }
      }
    }

    return [
      'data' => $target,
      'changed' => $changed
    ];
  }
}
