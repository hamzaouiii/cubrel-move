<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use App\Models\Module;

class ModuleScaffolder
{
  public function __construct(protected Filesystem $files) {}

  public function scaffold(Module $module, string $label): void
  {
    $slug = $module->slug;
    $modelClass = $module->model_class;
    $table = $module->table_name;

    $baseName   = class_basename($modelClass);

    $this->createModelFile($baseName, $table);
    $this->createHandlerFile($baseName, $modelClass);
    $this->createLangFiles($baseName, $label);
    $this->createTable($table);
  }

  protected function createModelFile(string $baseName, string $table): void
  {
    $directory = app_path('Models/Modules/Custom');

    if (! $this->files->exists($directory)) {
      $this->files->makeDirectory($directory, 0755, true);
    }

    $path = $directory . "/{$baseName}.php";

    if ($this->files->exists($path)) {
      return;
    }

    $contents = <<<PHP
        <?php

        namespace App\Models\\Modules\\Custom;

        use App\\Models\\BaseModule;

        class {$baseName} extends BaseModule
        {
            protected \$table = '{$table}';

            protected \$guarded = [];
        }

        PHP;

    $this->files->put($path, $contents);
  }

  protected function createHandlerFile(string $baseName, string $modelClass): void
  {
    $directory = app_path('Handlers/Modules/Custom');

    if (! $this->files->exists($directory)) {
      $this->files->makeDirectory($directory, 0755, true);
    }

    $handlerName = "{$baseName}ModuleHandler";
    $path = $directory . "/{$handlerName}.php";

    if ($this->files->exists($path)) {
      return;
    }

    $contents = <<<PHP
        <?php

        namespace App\Handlers\Modules\Custom;

        use {$modelClass};
        use Illuminate\Database\Eloquent\Builder;
        use App\Handlers\Modules\BasePaginatedModuleHandler;

        class {$handlerName} extends BasePaginatedModuleHandler
        {
            protected string \$model = {$baseName}::class;

            protected function query(array \$params = []): Builder
            {
                \$query = {$baseName}::query();

                // apply filters if needed

                return \$query;
            }
        }

        PHP;

    $this->files->put($path, $contents);
  }

  protected function createTable(string $table): void
  {
    if (Schema::hasTable($table)) {
      return;
    }

    Schema::create($table, function (Blueprint $tableBlueprint) {
      $tableBlueprint->uuid('id')->primary();
      $tableBlueprint->string('name')->nullable();
      $tableBlueprint->text('description')->nullable();
      $tableBlueprint->timestamps();
      $tableBlueprint->softDeletes();
    });
  }

  protected function createLangFiles(string $baseName, string $label): void
  {
    $langPath   = base_path('lang');
    $locales    = array_filter(scandir($langPath), function ($item) use ($langPath) {
      return $item !== '.'
        && $item !== '..'
        && is_dir($langPath . '/' . $item);
    });

    foreach ($locales as $locale) {

      $directory = $langPath . "/{$locale}/custom/modules";

      if (! $this->files->exists($directory)) {
        $this->files->makeDirectory($directory, 0755, true);
      }

      $path = $directory . "/{$baseName}.php";

      if ($this->files->exists($path)) {
        continue;
      }

      $contents = <<<'PHP'
<?php
return [
    'label' => 'LABEL_VALUE',
];
PHP;

      $contents = str_replace('LABEL_VALUE', $label, $contents);
      $this->files->put($path, $contents);
    }
  }
}
