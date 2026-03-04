<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixModuleLayouts extends Command
{
  protected $signature = 'layouts:fix';
  protected $description = 'Replace panelHeader with fields and remove relationship arrays';

  public function handle()
  {
    $path = config_path('module_layouts');
    $files = glob($path . '/*.php');

    foreach ($files as $file) {

      $data = include $file;

      $data = $this->transformArray($data);

      $export = "<?php\n\nreturn " . var_export($data, true) . ";\n";

      file_put_contents($file, $export);

      $this->info("Processed: {$file}");
    }

    $this->info('Done.');
  }

  private function transformArray(array $array): array
  {
    $result = [];

    foreach ($array as $key => $value) {

      // remove relationship
      if ($key === 'key') {
        continue;
      }
      if (is_array($value)) {
        $value = $this->transformArray($value);
      }

      $result[$key] = $value;
    }

    return $result;
  }
}
