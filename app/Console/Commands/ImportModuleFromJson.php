<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Label;
use App\Models\Module;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\ModuleScaffolder;
use App\Services\Relationships\RelationshipService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ImportModuleFromJson extends Command
{
    protected $signature = 'modules:import {path : Path to a module definition JSON file}';

    protected $description = 'Creates or updates a module (and its fields) from a JSON definition file — for quickly spinning up test/demo modules from code';

    public function handle(ModuleScaffolder $scaffolder): int
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
        $tableName = $data['table_name'] ?? ('cstm_'.$slug);

        // Whether the table is already there decides how fields get stored below:
        // pre-existing table -> no schema change possible -> fields go through
        // custom_fields JSON. Fresh table -> fields get real columns, same as
        // the module builder's draft -> activate pipeline.
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
            'handler_class' => $data['handler_class'] ?? "App\\Handlers\\Modules\\Custom\\{$baseName}ModuleHandler",
            'description' => $data['description'] ?? '',
            'model_class' => $data['model_class'] ?? "App\\Models\\Modules\\Custom\\{$baseName}",
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

        $this->info(($module->wasRecentlyCreated ? 'Created' : 'Updated')." module [{$slug}]");

        $fields = $data['fields'] ?? [];

        if (! $tableExisted) {
            $this->createDraftFields($module, $fields);

            // createTable() reads draft fields to build real columns, so it must
            // run — and model-file cast detection must happen — before
            // activateFields() flips them out of draft state.
            $scaffolder->createTable($tableName, $module);

            if ($isCustom) {
                $scaffolder->createModelFile($baseName, $tableName, $module);
                $scaffolder->createHandlerFile($baseName, $module->model_class);
                $scaffolder->createModuleLabels($module);
            }

            $scaffolder->activateFields($module);

            $this->info("Created table [{$tableName}] with ".count($fields).' field(s) as real columns.');
        } else {
            $created = $this->syncFieldsAgainstExistingTable($module, $fields);

            $this->info("Table [{$tableName}] already existed — {$created} new field(s) added as custom_fields (no schema change), existing fields left untouched.");
        }

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
     * Insert fields as drafts so ModuleScaffolder::createTable() picks them up
     * and gives each one a real column.
     */
    protected function createDraftFields(Module $module, array $fields): void
    {
        foreach ($fields as $fieldKey => $def) {
            $name = $def['name'] ?? $fieldKey;

            Field::updateOrCreate(
                ['module_id' => $module->id, 'name' => $name],
                array_merge($this->commonFieldAttributes($def, $name), [
                    'key' => "{$module->slug}_{$name}",
                    'label' => $def['label'] ?? Str::headline($name),
                    'is_draft' => true,
                    'is_active' => false,
                ])
            );
        }
    }

    /**
     * Table pre-existed (core-style module, or re-running against an already
     * scaffolded one) — no schema changes. New fields are added as
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
                $existing->fill($this->commonFieldAttributes($def, $name))->save();

                continue;
            }

            $labelText = $def['label'] ?? Str::headline($name);
            $labelKey = "modules.{$module->slug}.fields.{$name}";

            Label::updateOrCreate(
                ['key' => $labelKey, 'module_id' => $module->id],
                ['value' => $labelText, 'is_custom' => true]
            );

            Field::create(array_merge($this->commonFieldAttributes($def, $name), [
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

    protected function commonFieldAttributes(array $def, string $name): array
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
}
