<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Label;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Reads a module definition JSON file and materializes it: the modules/fields
 * rows, the DB table, and — unlike the module builder — real PHP model +
 * handler files, targeted at either the Custom namespace (is_custom: true,
 * gitignored, matches the in-app module builder) or the core Modules
 * namespace (is_custom: false, committed to git, for hand-off-ready test
 * modules). Deliberately does not use App\Services\ModuleScaffolder: that
 * service only ever targets the Custom namespace, which is correct for the
 * runtime module builder but wrong here.
 */
class ImportModuleFromJson extends Command
{
    protected $signature = 'modules:import {path : Path to a module definition JSON file}';

    protected $description = 'Creates or updates a module (and its fields) from a JSON definition file — supports both custom and core-style modules, for testing';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        $data = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }

        if (empty($data['name']) || empty($data['label'])) {
            $this->error('"name" and "label" are the only required keys.');

            return self::FAILURE;
        }

        $slug = $data['slug'] ?? Str::slug($data['name'], '_');
        $baseName = Str::studly($slug);
        $isCustom = (bool) ($data['is_custom'] ?? true);
        $hasLineItems = (bool) ($data['has_line_items'] ?? false);

        $tableName = $data['table_name'] ?? ($isCustom ? "cstm_{$slug}" : $slug);
        $modelClass = $data['model_class'] ?? ($isCustom
            ? "App\\Models\\Modules\\Custom\\{$baseName}"
            : "App\\Models\\Modules\\{$baseName}");
        $handlerClass = $data['handler_class'] ?? ($isCustom
            ? "App\\Handlers\\Modules\\Custom\\{$baseName}ModuleHandler"
            : "App\\Handlers\\Modules\\{$baseName}ModuleHandler");

        // Pre-existing table -> no schema change possible -> new fields go
        // through custom_fields JSON. Fresh table -> fields get real columns.
        $tableExisted = Schema::hasTable($tableName);

        $attributes = [
            'name' => $data['name'],
            'label' => $data['label'],
            'single_label' => $data['single_label'] ?? $data['label'],
            'icon' => $data['icon'] ?? 'fa-solid fa-cube',
            'color' => $data['color'] ?? '#0d6efd',
            'path' => $data['path'] ?? ('/'.$slug),
            'sort_order' => $data['sort_order'] ?? ((Module::max('sort_order') ?? 0) + 1),
            'category' => $data['category'] ?? 'custom',
            'is_active' => $data['is_active'] ?? true,
            'is_draft' => false,
            'has_activity' => $data['has_activity'] ?? false,
            'is_activity' => $data['is_activity'] ?? false,
            'show_in_sidebar' => $data['show_in_sidebar'] ?? true,
            'show_in_module_manager' => $data['show_in_module_manager'] ?? true,
            'handler_class' => $handlerClass,
            'description' => $data['description'] ?? '',
            'model_class' => $modelClass,
            'table_name' => $tableName,
            'is_custom' => $isCustom,
            'is_relatable' => $data['is_relatable'] ?? true,
            'has_owner' => $data['has_owner'] ?? true,
            'is_product_like' => $data['is_product_like'] ?? false,
            'has_line_items' => $hasLineItems,
            'line_item_source_module' => $hasLineItems ? ($data['line_item_source_module'] ?? 'products') : null,
        ];

        $module = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->updateOrCreate(['slug' => $slug], $attributes);

        $this->info(($module->wasRecentlyCreated ? 'Created' : 'Updated')." module [{$slug}] (".($isCustom ? 'custom' : 'core').')');

        $fields = $data['fields'] ?? [];

        if (! $tableExisted) {
            $this->createDraftFields($module, $fields);
            $this->buildTable($module);

            if ($isCustom) {
                $this->createModuleLabelRows($module);
            }

            $this->activateDraftFields($module);

            $this->info("Created table [{$tableName}] with ".count($fields).' field(s) as real columns.');
        } else {
            $created = $this->syncFieldsAgainstExistingTable($module, $fields);

            $this->info("Table [{$tableName}] already existed — {$created} new field(s) added as custom_fields (no schema change), existing fields left untouched.");
        }

        // Idempotent regardless of branch above: fills in the model/handler
        // file only if missing, so a hand-written core class is never touched.
        $this->buildModelFile($module);
        $this->buildHandlerFile($module);

        if ($module->has_activity || $module->is_activity) {
            RelationshipService::syncActivityRelationships($module);
            $this->info('Synced activity relationships (has_activity <-> is_activity modules).');
        }

        if ($hasLineItems) {
            $this->info("has_line_items is on — default line item fields (subtotal/discount/tax/total) and the '{$module->line_item_source_module}' picker fall back automatically, nothing else to generate.");
        }

        $this->info("Done. Module available at {$module->path}");

        return self::SUCCESS;
    }

    /**
     * Insert fields as drafts so buildTable() picks them up and gives each
     * one a real column.
     */
    protected function createDraftFields(Module $module, array $fields): void
    {
        foreach ($fields as $fieldKey => $def) {
            $name = $def['name'] ?? $fieldKey;

            Field::updateOrCreate(
                ['module_id' => $module->id, 'name' => $name],
                array_merge($this->commonFieldAttributes($def), [
                    'key' => "{$module->slug}_{$name}",
                    'label' => $def['label'] ?? Str::headline($name),
                    'is_draft' => true,
                    'is_active' => false,
                ])
            );
        }
    }

    /**
     * Table pre-existed — no schema changes. New fields are added as
     * custom_fields-JSON-backed; fields that already exist are left alone
     * beyond their descriptive metadata, since they may already map to a real
     * column with live data.
     */
    protected function syncFieldsAgainstExistingTable(Module $module, array $fields): int
    {
        $created = 0;

        foreach ($fields as $fieldKey => $def) {
            $name = $def['name'] ?? $fieldKey;

            $existing = Field::where('module_id', $module->id)->where('name', $name)->first();

            if ($existing) {
                $existing->fill($this->commonFieldAttributes($def))->save();

                continue;
            }

            $labelText = $def['label'] ?? Str::headline($name);
            $labelKey = "modules.{$module->slug}.fields.{$name}";

            Label::updateOrCreate(
                ['key' => $labelKey, 'module_id' => $module->id],
                ['value' => $labelText, 'is_custom' => true]
            );

            Field::create(array_merge($this->commonFieldAttributes($def), [
                'module_id' => $module->id,
                'name' => $name,
                'key' => "{$module->slug}_{$name}",
                'label' => $labelKey,
                'is_draft' => false,
                'is_active' => true,
                'is_custom' => true,
            ]));

            $created++;
        }

        return $created;
    }

    protected function commonFieldAttributes(array $def): array
    {
        return [
            'type' => $def['type'] ?? 'text',
            'required' => $def['required'] ?? false,
            'readonly' => $def['readonly'] ?? false,
            'hidden' => $def['hidden'] ?? false,
            'searchable' => $def['searchable'] ?? false,
            'filterable' => $def['filterable'] ?? false,
            'sortable' => $def['sortable'] ?? false,
            'default_value' => $def['default_value'] ?? null,
            'min_length' => $def['min_length'] ?? null,
            'max_length' => $def['max_length'] ?? null,
            'regex' => $def['regex'] ?? null,
            'related_module' => $def['related_module'] ?? null,
        ];
    }

    /**
     * Flip drafted fields to active, same bookkeeping as
     * ModuleScaffolder::activateFields() — key = "{slug}_{name}", a Label row
     * carrying the human-readable text, field->label rewritten to the
     * translation key.
     */
    protected function activateDraftFields(Module $module): void
    {
        foreach ($module->draftFields() as $field) {
            $field->is_draft = false;
            $field->is_active = true;
            $field->key = "{$module->slug}_{$field->name}";

            $labelKey = "modules.{$module->slug}.fields.{$field->name}";

            Label::updateOrCreate(
                ['key' => $labelKey, 'module_id' => $module->id],
                ['value' => $field->label, 'is_custom' => true]
            );

            $field->label = $labelKey;
            $field->save();
        }
    }

    protected function createModuleLabelRows(Module $module): void
    {
        Label::updateOrCreate(
            ['key' => "modules.{$module->slug}.label", 'module_id' => $module->id],
            ['value' => $module->getRawOriginal('name'), 'is_custom' => true]
        );

        Label::updateOrCreate(
            ['key' => "modules.{$module->slug}.single_label", 'module_id' => $module->id],
            ['value' => $module->getRawOriginal('name'), 'is_custom' => true]
        );
    }

    protected function buildTable(Module $module): void
    {
        if (Schema::hasTable($module->table_name)) {
            return;
        }

        $typeMapper = config('default_field_types_mapper');
        $fields = $module->draftFields();
        $hasLineItems = $module->has_line_items;

        Schema::create($module->table_name, function (Blueprint $table) use ($fields, $typeMapper, $hasLineItems) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            foreach ($fields as $field) {
                $key = $field['key'] ?? null;
                $name = $field['name'] ?? null;

                if (! $key || str_starts_with($key, 'default.')) {
                    continue;
                }

                $blueprintMethod = $typeMapper[$field['type'] ?? 'text'] ?? 'string';
                $table->{$blueprintMethod}($name)->nullable();
            }

            if ($hasLineItems) {
                $table->decimal('subtotal', 15, 2)->nullable();
                $table->decimal('discount_amount', 15, 2)->nullable();
                $table->decimal('tax_amount', 15, 2)->nullable();
                $table->decimal('total', 15, 2)->nullable();
            }

            $table->json('custom_fields')->nullable();
            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->index('owner_id');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Directory/namespace are derived from the module's own model_class, so
     * this lands in App\Models\Modules\Custom for custom modules and
     * App\Models\Modules directly for core ones — never touches a file that
     * already exists, so a hand-written core model is safe to re-import over.
     */
    protected function buildModelFile(Module $module): void
    {
        $path = $this->classFilePath($module->model_class);

        if (File::exists($path)) {
            return;
        }

        File::ensureDirectoryExists(dirname($path));

        $namespace = Str::beforeLast($module->model_class, '\\');
        $baseName = class_basename($module->model_class);
        $castsExport = $this->exportCastsArray($this->castsForModule($module));

        $contents = <<<PHP
        <?php

        namespace {$namespace};

        use App\Models\BaseModule;

        class {$baseName} extends BaseModule
        {
            protected \$table = '{$module->table_name}';

            protected \$guarded = [];

            protected \$moduleCasts = {$castsExport};
        }

        PHP;

        File::put($path, $contents);
        $this->info('Wrote model '.$module->model_class.' -> '.$path);
    }

    protected function buildHandlerFile(Module $module): void
    {
        $path = $this->classFilePath($module->handler_class);

        if (File::exists($path)) {
            return;
        }

        File::ensureDirectoryExists(dirname($path));

        $namespace = Str::beforeLast($module->handler_class, '\\');
        $handlerBaseName = class_basename($module->handler_class);
        $modelClass = $module->model_class;
        $modelBaseName = class_basename($modelClass);

        $contents = <<<PHP
        <?php

        namespace {$namespace};

        use {$modelClass};
        use Illuminate\Database\Eloquent\Builder;
        use App\Handlers\Modules\BaseModuleHandler;

        class {$handlerBaseName} extends BaseModuleHandler
        {
            protected string \$model = {$modelBaseName}::class;

            public function query(array \$params = []): Builder
            {
                \$query = {$modelBaseName}::query();

                // apply filters if needed

                return \$query;
            }
        }

        PHP;

        File::put($path, $contents);
        $this->info('Wrote handler '.$module->handler_class.' -> '.$path);
    }

    /**
     * Maps a fully qualified class name onto its expected file path under
     * app/, e.g. "App\Models\Modules\Move" -> app/Models/Modules/Move.php.
     * Works the same whether the namespace includes a \Custom\ segment or not.
     */
    protected function classFilePath(string $fqcn): string
    {
        $relative = Str::after($fqcn, 'App\\');

        return app_path(str_replace('\\', '/', $relative).'.php');
    }

    protected function castsForModule(Module $module): array
    {
        $typeToCast = [
            'date' => 'date',
            'datetime' => 'datetime',
            'number' => 'integer',
            'integer' => 'integer',
            'duration' => 'integer',
            'decimal' => 'decimal:2',
            'currency' => 'decimal:2',
            'percentage' => 'decimal:2',
            'checkbox' => 'boolean',
        ];

        return $module->draftFields()
            ->filter(fn ($field) => ! str_starts_with($field->key ?? '', 'default.') && isset($typeToCast[$field->type]))
            ->mapWithKeys(fn ($field) => [$field->name => $typeToCast[$field->type]])
            ->all();
    }

    protected function exportCastsArray(array $casts): string
    {
        if (empty($casts)) {
            return '[]';
        }

        $lines = array_map(
            fn ($name, $cast) => "        '{$name}' => '{$cast}',",
            array_keys($casts),
            $casts
        );

        return "[\n".implode("\n", $lines)."\n    ]";
    }
}
