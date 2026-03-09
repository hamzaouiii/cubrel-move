<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixFieldConfig extends Command
{
  protected $signature = 'config:fix-fields';
  protected $description = 'Fix field config (readonly timestamps + required searchable name fields)';

  public function handle()
  {
    $file = config_path('stock_fields.php');

    if (!file_exists($file)) {
      $this->error("Config file not found: {$file}");
      return;
    }

    $config = include $file;

    foreach ($config as $module => &$fields) {

      foreach ($fields as $fieldKey => &$field) {

        // timestamps readonly
        if (in_array($fieldKey, ['created_at', 'updated_at'])) {
          $field['readonly'] = true;
        }

        // name field rules
        if ($fieldKey === 'name') {
          $field['required'] = true;
          $field['searchable'] = true;
        }
      }
    }

    $export = "<?php\n\nreturn " . var_export($config, true) . ";\n";

    file_put_contents($file, $export);

    $this->info('Config updated successfully.');
  }
}
