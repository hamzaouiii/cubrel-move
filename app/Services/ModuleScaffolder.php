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
        use App\Handlers\Modules\BaseModuleHandler;

        class {$handlerName} extends BaseModuleHandler
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

  protected function createTable(string $table, Module $module): void
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

  protected function createModuleLabels(Module $module): void
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


  protected function activateFields(Module $module): void
  {
    // 1. Get fields for module. 
    // (Note: Add ->get() if draftFields() returns a query builder/relation)
    $fields = $module->draftFields();

    foreach ($fields as $field) {
      // 1. Update status flags
      $field->is_draft = false;
      $field->is_active = true;

      // 2. Rename field key 
      // Replaces the "draft" concept with the module slug and field name
      $field->key = $module->slug . '_' . $field->name;

      // 3. Handle 'select' type and related dropdown
      if ($field->type === 'select' && $field->dropdown_list_id) {
        $dropdown = $field->dropdown_list;

        // Assuming you want to un-draft the dropdown to activate it alongside the field
        if ($dropdown && $dropdown->is_draft) {
          $dropdown->is_draft = false;
          $dropdown->save();
        }
      }

      // 4. Handle Labels
      $label_key = "modules." . $module->slug . ".fields." . $field->name;
      $label_value = $field->label;

      // updateOrCreate takes two arrays: [Search attributes], [Values to update/insert]
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

      // 5. Assign the new label key back to the field
      $field->label = $label_key;

      // 6. Save the updated field to the database
      $field->save();
    }
  }
}
