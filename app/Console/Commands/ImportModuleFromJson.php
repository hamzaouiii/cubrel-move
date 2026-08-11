<?php

namespace App\Console\Commands;

use App\Models\DropdownList;
use App\Models\Field;
use App\Models\Label;
use App\Models\Layout;
use App\Models\Module;
use App\Models\Relationship;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
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
 *
 * Also accepts optional "layouts" and "relationships" keys on the same
 * definition, so a module plus its fields, layouts and relationships to
 * other (already-imported) modules can all come from one JSON file. Both
 * are re-runnable: layouts key on module+type same as LayoutManagerController,
 * relationships key on their unique name same as RelationshipManagerController.
 * Dev-only tool — not meant to run against production data.
 */
class ImportModuleFromJson extends Command
{
    use ConfirmableTrait;

    protected $signature = 'modules:import {path : Path to a module definition JSON file} {--force : Allow running outside local/testing without a confirmation prompt}';

    protected $description = 'Dev tool: creates or updates a module, its fields, layouts and relationships from a JSON definition file — supports both custom and core-style modules, for testing';

    public function handle(): int
    {
        if (! $this->confirmToProceed('This is a dev-only scaffolding tool and is not meant to run against production data.')) {
            return self::FAILURE;
        }

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

        // Unconditional and idempotent (updateOrCreate): every module needs its
        // "modules.{slug}.label"/"single_label" Label rows regardless of
        // is_custom, and regardless of whether the table already existed —
        // otherwise __() and the frontend t() fallback have nothing to resolve
        // and the raw translation key leaks into the UI (e.g. the sidebar,
        // which renders module.label directly without going through t()).
        $this->createModuleLabelRows($module);

        if (! $tableExisted) {
            $this->createDraftFields($module, $fields);
            $this->buildTable($module);
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

        $this->importLayouts($module, $data['layouts'] ?? []);
        $this->importRelationships($module, $data['relationships'] ?? []);

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
                array_merge($this->commonFieldAttributes($module, $name, $def), [
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
                $existing->fill($this->commonFieldAttributes($module, $name, $def))->save();

                continue;
            }

            $labelText = $def['label'] ?? Str::headline($name);
            $labelKey = "modules.{$module->slug}.fields.{$name}";

            Label::updateOrCreate(
                ['key' => $labelKey, 'module_id' => $module->id],
                ['value' => $labelText, 'is_custom' => true]
            );

            Field::create(array_merge($this->commonFieldAttributes($module, $name, $def), [
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

    protected function commonFieldAttributes(Module $module, string $name, array $def): array
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
            'dropdown_list_id' => $this->resolveDropdownListId($module, $name, $def),
        ];
    }

    /**
     * select/status fields resolve their dropdown by convention —
     * "{module_slug}_{field_name}_list" — same lookup StockFieldsSeeder uses,
     * unless the field definition names one explicitly via "dropdown_list".
     *
     * If the field def carries an inline "options" (keyed map, the module
     * builder / status field shape: {key: {label, icon, bg_color,
     * text_color}}) or "values" (already the DropdownList array shape:
     * [{value, label, color, bgColor, icon}]), the list is upserted from
     * that definition — re-running overwrites its values, same as
     * importLayouts(). Without either, an existing list is looked up and a
     * missing one just warns, unchanged from before.
     */
    protected function resolveDropdownListId(Module $module, string $name, array $def): ?string
    {
        $type = $def['type'] ?? 'text';

        if (! in_array($type, ['select', 'status'], true)) {
            return null;
        }

        $key = $def['dropdown_list'] ?? "{$module->slug}_{$name}_list";

        $values = $this->normalizeDropdownValues($def);

        if ($values !== null) {
            $list = DropdownList::updateOrCreate(
                ['key' => $key],
                [
                    'values' => $values,
                    'is_draft' => false,
                    'is_status' => $type === 'status',
                ]
            );

            $this->info("Saved dropdown list [{$key}] with ".count($values).' option(s).');

            return $list->id;
        }

        $id = DropdownList::where('key', $key)->value('id');

        if (! $id) {
            $this->warn("No dropdown list found for key [{$key}] (field '{$name}') — leaving dropdown_list_id empty.");
        }

        return $id;
    }

    /**
     * Accepts either shape and normalizes to the DropdownList "values" array
     * ([{value, label, color, bgColor, icon}]) the frontend (StatusField.vue
     * et al.) reads. Returns null when the field def has neither, so the
     * caller falls back to looking an existing list up instead.
     */
    protected function normalizeDropdownValues(array $def): ?array
    {
        if (isset($def['values']) && is_array($def['values'])) {
            return array_values($def['values']);
        }

        if (! isset($def['options']) || ! is_array($def['options'])) {
            return null;
        }

        $values = [];

        foreach ($def['options'] as $optionKey => $option) {
            $option = is_array($option) ? $option : [];

            $entry = [
                'value' => $option['value'] ?? $optionKey,
                'label' => $option['label'] ?? Str::headline((string) $optionKey),
            ];

            if (isset($option['color']) || isset($option['text_color'])) {
                $entry['color'] = $option['color'] ?? $option['text_color'];
            }

            if (isset($option['bgColor']) || isset($option['bg_color'])) {
                $entry['bgColor'] = $option['bgColor'] ?? $option['bg_color'];
            }

            if (isset($option['icon'])) {
                $entry['icon'] = $option['icon'];
            }

            $values[] = $entry;
        }

        return $values;
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

    /**
     * "layouts" is a map of layout type => definition, the exact shape
     * LayoutManagerController::store() saves and config/default_layouts.php /
     * config/module_layouts/*.php already use — e.g.
     * {"list": {"columns": [...]}, "record": {"sections": [...]}}. Keyed on
     * module_id + type, same as the layout editor, so re-running overwrites.
     */
    protected function importLayouts(Module $module, array $layouts): void
    {
        $requiredKeyByType = [
            'list' => 'columns',
            'linkingPanel' => 'columns',
            'related' => 'columns',
            'record' => 'sections',
            'lineItemsSnapshot' => 'fields',
        ];

        foreach ($layouts as $type => $definition) {
            if (! isset($requiredKeyByType[$type])) {
                $this->warn("Unknown layout type [{$type}] — skipping. Valid types: ".implode(', ', array_keys($requiredKeyByType)));

                continue;
            }

            $requiredKey = $requiredKeyByType[$type];

            if (! is_array($definition) || ! isset($definition[$requiredKey]) || ! is_array($definition[$requiredKey])) {
                $this->warn("Layout [{$type}] is missing its required \"{$requiredKey}\" array — skipping.");

                continue;
            }

            // Layout::module_id isn't fillable (see LayoutManagerController::store()),
            // so it's assigned directly rather than through updateOrCreate()'s mass assignment.
            $layout = Layout::firstOrNew(['module_id' => $module->id, 'type' => $type]);
            $layout->module_id = $module->id;
            $layout->module_name = $module->slug;
            $layout->type = $type;
            $layout->definition = $definition;
            $layout->save();

            $this->info("Saved [{$type}] layout for module [{$module->slug}].");
        }
    }

    /**
     * "relationships" is a list of relationship definitions with this module
     * as one side. Mirrors RelationshipManagerController::store(): "left_module"
     * defaults to this module's slug, "many-to-one" is normalized into
     * "one-to-many" with sides swapped (the DB only knows one-to-many), and
     * name defaults to "{left}_{right}" so re-running is idempotent via the
     * relationships.name unique key.
     */
    protected function importRelationships(Module $module, array $relationships): void
    {
        $validTypes = config('default_relationship_types');

        foreach ($relationships as $def) {
            if (empty($def['right_module']) || empty($def['type'])) {
                $this->warn('Relationship definition is missing "right_module" or "type" — skipping: '.json_encode($def));

                continue;
            }

            if (! in_array($def['type'], $validTypes, true)) {
                $this->warn("Unknown relationship type [{$def['type']}] — skipping. Valid types: ".implode(', ', $validTypes));

                continue;
            }

            $leftSlug = $def['left_module'] ?? $module->slug;
            $rightSlug = $def['right_module'];
            $type = $def['type'];

            if ($type === 'many-to-one') {
                [$leftSlug, $rightSlug] = [$rightSlug, $leftSlug];
                $type = 'one-to-many';
            }

            if ($leftSlug === $rightSlug) {
                $this->warn("Relationship [{$leftSlug} <-> {$rightSlug}] is self-referencing — skipping.");

                continue;
            }

            if (! Module::withoutGlobalScope(AdminOnlyModuleScope::class)->where('slug', $rightSlug)->exists()
                || ! Module::withoutGlobalScope(AdminOnlyModuleScope::class)->where('slug', $leftSlug)->exists()) {
                $this->warn("Relationship [{$leftSlug} <-> {$rightSlug}] references a module that doesn't exist yet — skipping.");

                continue;
            }

            $duplicate = Relationship::query()
                ->where('left_module', $leftSlug)
                ->where('right_module', $rightSlug)
                ->where('type', $type)
                ->where('name', '!=', $def['name'] ?? "{$leftSlug}_{$rightSlug}")
                ->exists();

            if ($duplicate) {
                $this->warn("Relationship [{$leftSlug} <-> {$rightSlug}] ({$type}) already exists under a different name — skipping.");

                continue;
            }

            $name = $def['name'] ?? "{$leftSlug}_{$rightSlug}";

            Relationship::updateOrCreate(
                ['name' => $name],
                [
                    'label' => $def['label'] ?? Str::headline($rightSlug),
                    'left_module' => $leftSlug,
                    'right_module' => $rightSlug,
                    'type' => $type,
                    'join_table' => 'relationship_links',
                    'is_system' => $def['is_system'] ?? false,
                ]
            );

            $this->info("Saved relationship [{$name}] ({$leftSlug} {$type} {$rightSlug}).");
        }
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
            'address' => 'array',
            'multivalue' => 'array',
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
