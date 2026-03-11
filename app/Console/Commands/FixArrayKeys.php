<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixArrayKeys extends Command
{
  protected $signature = 'layout:fix-keys {path=config/module_layouts}';
  protected $description = 'Fix incorrect numeric array keys in layout config files';

  public function handle()
  {
    $path = base_path($this->argument('path'));

    if (!File::exists($path)) {
      $this->error("Path not found: {$path}");
      return 1;
    }

    if (File::isFile($path)) {
      $files = [new \SplFileInfo($path)];
    } else {
      $files = File::allFiles($path);
    }

    foreach ($files as $file) {
      $data = include $file->getRealPath();

      $fixed = $this->reindexRecursive($data);

      $export = "<?php\n\nreturn " . var_export($fixed, true) . ";\n";

      File::put($file->getRealPath(), $export);

      $this->info("Fixed: " . $file->getFilename());
    }

    $this->info('Done.');

    return 0;
  }

  private function reindexRecursive(array $array): array
  {
    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $array[$key] = $this->reindexRecursive($value);
      }
    }

    if (array_is_list($array)) {
      return array_values($array);
    }

    return $array;
  }
}
