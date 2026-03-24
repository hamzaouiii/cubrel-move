<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveFieldsFromConfig extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'fields:remove {keys*} {--config=} {--file=}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Remove fields from configuration array based on array keys';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $keysToRemove = $this->argument('keys');
    $configPath = $this->option('config');
    $filePath = $this->option('file');

    // Load the configuration array
    if ($filePath) {
      $config = $this->loadFromFile($filePath);
    } elseif ($configPath) {
      $config = config($configPath);
    } else {
      $this->error('Please specify either --config or --file option');
      return 1;
    }

    if (!$config) {
      $this->error('Configuration not found');
      return 1;
    }

    // Remove fields from each section
    $modified = $this->removeFields($config, $keysToRemove);

    // Display results
    $this->info('Removed fields: ' . implode(', ', $keysToRemove));
    $this->newLine();

    // Output the modified array
    if ($filePath) {
      $this->saveToFile($filePath, $modified);
      $this->info("Saved to file: {$filePath}");
    } else {
      $this->line(json_encode($modified, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    return 0;
  }

  /**
   * Remove specified fields from the configuration array
   */
  private function removeFields(array $config, array $keysToRemove): array
  {
    $modified = [];

    foreach ($config as $section => $fields) {
      if (!is_array($fields)) {
        $modified[$section] = $fields;
        continue;
      }

      $modified[$section] = array_diff_key($fields, array_flip($keysToRemove));

      $this->line("Section '{$section}': removed " .
        (count($fields) - count($modified[$section])) . " fields");
    }

    return $modified;
  }

  /**
   * Load configuration from a PHP file
   */
  private function loadFromFile(string $filePath): ?array
  {
    if (!file_exists($filePath)) {
      $this->error("File not found: {$filePath}");
      return null;
    }

    return require $filePath;
  }

  /**
   * Save modified configuration to a PHP file
   */
  private function saveToFile(string $filePath, array $data): void
  {
    $content = "<?php\n\nreturn " . $this->varExport($data) . ";\n";
    file_put_contents($filePath, $content);
  }

  /**
   * Export array as PHP code
   */
  private function varExport($expression, $return = true)
  {
    $export = var_export($expression, true);
    $export = preg_replace("/^array \(/", '[', $export);
    $export = preg_replace("/\)$/", ']', $export);
    $export = preg_replace("/=> \n\s*\[/", '=> [', $export);
    $export = str_replace("array (", '[', $export);
    $export = str_replace(")", ']', $export);

    if ($return) {
      return $export;
    }

    echo $export;
  }
}
