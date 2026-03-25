<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use App\Models\Module;
use App\Models\Label;

class ModuleScaffolder
{
  public function __construct(protected Filesystem $files) {}

  public function scaffold(Module $module, string $label, string $single_label = ''): void
  {

    $slug = $module->slug;
    $modelClass = $module->model_class;
    $table = $module->table_name;

    $baseName = class_basename($modelClass);

    $this->createModelFile($baseName, $table);
    $this->createHandlerFile($baseName, $modelClass);
    $this->createModuleLabels($module);
    $this->activateFields($module);
    $this->createTable($table, $module);
  }

  public function createModelFile(string $baseName, string $table): void
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
    if (function_exists('opcache_invalidate')) {
      opcache_invalidate($path, true);
    }
  }

  public function createHandlerFile(string $baseName, string $modelClass): void
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
        use App\Handlers\Modules\BaseModuleHandler;

        class {$handlerName} extends BaseModuleHandler
        {
            protected string \$model = {$baseName}::class;

            public function query(array \$params = []): Builder
            {
                \$query = {$baseName}::query();

                // apply filters if needed

                return \$query;
            }
        }

        PHP;

    $this->files->put($path, $contents);
  }

  public function createTable(string $table, Module $module): void
  {
    if (Schema::hasTable($table)) {
      return;
    }


    $typeMapper = config('default_field_types_mapper');
    $fields = $module->draftFields();

    Schema::create($table, function (Blueprint $tableBlueprint) use ($fields, $typeMapper) {
      $tableBlueprint->uuid('id')->primary();
      $tableBlueprint->string('name')->nullable();
      $tableBlueprint->text('description')->nullable();

      foreach ($fields as $field) {
        $key = $field['key'] ?? null;

        if (!$key || str_starts_with($key, 'default.')) {
          continue;
        }

        $fieldType = $field['type'] ?? 'text';

        $blueprintMethod = $typeMapper[$fieldType] ?? 'string';

        $column = $tableBlueprint->{$blueprintMethod}($key);
        $column->nullable();
      }
      //for custom fields
      $tableBlueprint->json('custom_fields')->nullable();

      $tableBlueprint->timestamps();
      $tableBlueprint->softDeletes();
    });
  }

  public function createModuleLabels(Module $module): void
  {
    $label_key = "modules." . $module->slug . ".label";
    $single_label_key = "modules." . $module->slug . ".single_label";


    // updateOrCreate takes two arrays: [Search attributes], [Values to update/insert]
    Label::updateOrCreate(
      [
        'key' => $label_key,
        'module_id' => $module->id,
      ],
      [
        'value' => $module->label,
        'is_custom' => true
      ]
    );

    Label::updateOrCreate(
      [
        'key' => $single_label_key,
        'module_id' => $module->id,
      ],
      [
        'value' => $module->single_label,
        'is_custom' => true
      ]
    );
  }


  public function activateFields(Module $module): void
  {
    // 1. Get fields for module. 
    $fields = $module->draftFields();

    foreach ($fields as $field) {
      $field->is_draft = false;
      $field->is_active = true;

      $field->key = $module->slug . '_' . $field->name;

      if ($field->type === 'select' && $field->dropdown_list_id) {
        $dropdown = $field->dropdown_list;

        // Assuming you want to un-draft the dropdown to activate it alongside the field
        if ($dropdown && $dropdown->is_draft) {
          $dropdown->is_draft = false;
          $dropdown->save();
        }
      }

      $label_key = "modules." . $module->slug . ".fields." . $field->name;
      $label_value = $field->label;

      Label::updateOrCreate(
        [
          'key' => $label_key,
          'module_id' => $module->id,
        ],
        [
          'value' => $label_value,
          'is_custom' => true
        ]
      );

      $field->label = $label_key;
      $field->save();
    }
  }

  public function rollback(Module $module): void
  {
    $baseName = class_basename($module->model_class);
    $table = $module->table_name;

    $this->files->delete(app_path("Models/Modules/Custom/{$baseName}.php"));
    $this->files->delete(app_path("Handlers/Modules/Custom/{$baseName}ModuleHandler.php"));

    Schema::dropIfExists($table);

    Label::where('module_id', $module->id)->delete();

    // 4. Reset Module State
    $module->update([
      'is_active' => false,
      'is_draft' => true,
      'table_name' => null,
    ]);
  }
}
