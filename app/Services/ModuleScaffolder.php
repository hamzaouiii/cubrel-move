<?php

namespace App\Services;

use App\Models\Field;
use App\Models\Label;
use App\Models\Module;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;

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

        $path = $directory."/{$baseName}.php";

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
        $path = $directory."/{$handlerName}.php";

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
        $hasLineItems = $module->has_line_items;

        Schema::create($table, function (Blueprint $tableBlueprint) use ($fields, $typeMapper, $hasLineItems) {
            $tableBlueprint->uuid('id')->primary();
            $tableBlueprint->string('name')->nullable();
            $tableBlueprint->text('description')->nullable();
            foreach ($fields as $field) {
                $key = $field['key'] ?? null;
                $name = $field['name'] ?? null;

                if (! $key || str_starts_with($key, 'default.')) {
                    continue;
                }

                $fieldType = $field['type'] ?? 'text';

                $blueprintMethod = $typeMapper[$fieldType] ?? 'string';

                $column = $tableBlueprint->{$blueprintMethod}($name);
                $column->nullable();
            }

            if ($hasLineItems) {
                $tableBlueprint->decimal('subtotal', 15, 2)->nullable();
                $tableBlueprint->decimal('discount_amount', 15, 2)->nullable();
                $tableBlueprint->decimal('tax_amount', 15, 2)->nullable();
                $tableBlueprint->decimal('total', 15, 2)->nullable();
            }
            // for custom fields
            $tableBlueprint->json('custom_fields')->nullable();
            $tableBlueprint->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $tableBlueprint->index('owner_id');
            $tableBlueprint->timestamps();
            $tableBlueprint->softDeletes();
        });
    }

    public function createModuleLabels(Module $module): void
    {
        $label_key = 'modules.'.$module->slug.'.label';
        $single_label_key = 'modules.'.$module->slug.'.single_label';

        Label::updateOrCreate(
            [
                'key' => $label_key,
                'module_id' => $module->id,
            ],
            [
                'value' => $module->label,
                'is_custom' => true,
            ]
        );

        Label::updateOrCreate(
            [
                'key' => $single_label_key,
                'module_id' => $module->id,
            ],
            [
                'value' => $module->single_label,
                'is_custom' => true,
            ]
        );
    }

    public function activateFields(Module $module): void
    {
        $fields = $module->draftFields();

        foreach ($fields as $field) {
            $field->is_draft = false;
            $field->is_active = true;

            $field->key = $module->slug.'_'.$field->name;

            if ($field->type === 'select' && $field->dropdown_list_id) {
                $dropdown = $field->dropdown_list;

                if ($dropdown && $dropdown->is_draft) {
                    $dropdown->is_draft = false;
                    $dropdown->save();
                }
            }

            $label_key = 'modules.'.$module->slug.'.fields.'.$field->name;
            $label_value = $field->label;

            Label::updateOrCreate(
                [
                    'key' => $label_key,
                    'module_id' => $module->id,
                ],
                [
                    'value' => $label_value,
                    'is_custom' => true,
                ]
            );

            $field->label = $label_key;
            $field->save();
        }
    }

    public function rollback(Module $module): void
    {
        // A draft discarded before ever reaching the deploy pipeline's
        // initialize step has neither a model_class nor a scaffolded table
        // nothing to unlink/drop in that case.
        if ($module->model_class) {
            $baseName = class_basename($module->model_class);

            $this->files->delete(app_path("Models/Modules/Custom/{$baseName}.php"));
            $this->files->delete(app_path("Handlers/Modules/Custom/{$baseName}ModuleHandler.php"));
        }

        $table = $module->table_name;

        if ($table) {
            Schema::dropIfExists($table);
        }

        Label::where('module_id', $module->id)->delete();

        $module->update([
            'is_active' => false,
            'is_draft' => true,
            'table_name' => null,
        ]);
    }

    /**
     * Abandons a draft module entirely. which resets a
     * module back to draft state, this removes the row itself so it stops
     * occupying a lock slot / showing up in future draft recycling.
     */
    public function discardDraft(Module $module): void
    {
        $this->rollback($module);

        Field::where('module_id', $module->id)->delete();

        $module->delete();
    }
}
