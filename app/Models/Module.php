<?php

namespace App\Models;

use App\Concerns\HasTranslatableLabel;
use App\Scopes\AdminOnlyModuleScope;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * This is an infrastructure class. A Module is an editable item that contains metadata for each module.
 * Not to be confused with BaseModule => app\Models\BaseModule.php which is a business module, all CRM modules are to extend it, unlike this one which probably needs to be an abstract class
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<Layout> $layouts
 * @property-read \Illuminate\Database\Eloquent\Collection<Field> $fields
 */
class Module extends Model
{
    use HasTranslatableLabel;
    use HasUuids;

    protected static array $staticFieldCache = [];

    protected $fillable = [
        'slug',
        'name',
        'icon',
        'label',
        'is_draft',
        'category',
        'single_label',
        'color',
        'path',
        'sort_order',
        'is_active',
        'description',
        'model_class',
        'handler_class',
        'table_name',
        'show_in_sidebar',
        'is_custom',
        'is_draft',
        'locked_by',
        'locked_until',
        'has_line_items',
        'has_owner',
        'is_product_like',
        'line_item_source_module',
        'show_in_module_manager',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $guarded = [];

    public $timestamps = true;

    public static function forSidebar(): Collection
    {
        return self::where('is_draft', 0)
            ->where('show_in_sidebar', 1)
            ->orderBy('category')
            ->get()
            ->map(function (Module $module) {

                return [
                    'slug' => $module->slug,
                    'name' => $module->name,
                    'icon' => $module->icon,
                    'color' => $module->color,
                    'path' => $module->path,
                    'label' => $module->label,
                    'single_label' => $module->single_label,
                    'category' => $module->category,
                ];
            })
            ->values();
    }

    /**
     * @return HasMany<Layout, $this>
     */
    public function layouts()
    {
        return $this->hasMany(Layout::class);
    }

    public function getDefaultLayout(string $type)
    {
        $layout = $this->layouts()->where('type', $type)->first();

        if ($layout !== null) {
            return $layout->definition;
        }

        // Module specific config fallback
        $moduleConfig = config("module_layouts.{$this->slug}");
        if (is_array($moduleConfig) && isset($moduleConfig[$type])) {
            return $moduleConfig[$type];
        }

        // Global fallback
        $globalDefault = Layout::getDefaultLayout($type);
        if ($globalDefault !== null) {
            return $globalDefault;
        }
    }

    public function listLayout(): array
    {
        return $this->resolveLayout('list');
    }

    public function recordLayout(): array
    {
        return $this->resolveLayout('record');
    }

    public function relatedLayout(): array
    {
        return $this->resolveLayout('related');
    }

    public function linkingPanelLayout(): array
    {
        return $this->resolveLayout('linkingPanel');
    }

    public function lineItemsSnapshotLayout(): array
    {
        return $this->resolveLayout('lineItemsSnapshot');
    }

    public function getDataForPanel(): array
    {
        return [
            'linkingPanel' => $this->linkingPanelLayout(),
            'fields' => $this->allFields(),
        ];
    }


    /**
     * @return HasMany<Field, $this>
     *                               excludes default fields
     */
    public function fields()
    {
        return $this->hasMany(Field::class, 'module_id', 'id')
            ->select([
                'id',
                'module_id',
                'dropdown_list_id',
                'related_module',
                'name',
                'type',
                'key',
                'readonly',
                'sortable',
                'searchable',
                'label',
                'required',
                'is_draft',

            ])
            ->with('dropdown_list');
    }

    /**
     * @return Collection<Field>
     */
    public function allFields(): Collection
    {
        $key = $this->id;
        $has_line_items = $this->has_line_items;

        if (! isset(self::$staticFieldCache[$key])) {
            self::$staticFieldCache[$key] = Field::query()
                ->where(function ($query) {
                    $query->where('module_id', $this->id)
                        ->orWhere('is_global', true);
                })
                ->select([
                    'id',
                    'module_id',
                    'dropdown_list_id',
                    'related_module',
                    'name',
                    'type',
                    'key',
                    'readonly',
                    'sortable',
                    'searchable',
                    'filterable',
                    'label',
                    'required',
                    'is_draft',
                    'is_calculated',
                    'related_module',
                ])
                ->with('dropdown_list')
                ->get();

            if ($has_line_items) {
                $lineItemFields = Field::query()
                    ->where('is_default_for_line_items', true)
                    ->select([
                        'id',
                        'module_id',
                        'dropdown_list_id',
                        'related_module',
                        'name',
                        'type',
                        'key',
                        'readonly',
                        'sortable',
                        'searchable',
                        'filterable',
                        'label',
                        'required',
                        'is_draft',
                        'related_module',
                    ])
                    ->get();

                self::$staticFieldCache[$key] = self::$staticFieldCache[$key]->merge($lineItemFields);
            }

        }

        return self::$staticFieldCache[$key];
    }

    public static function warmFieldsCache(Collection $modules): void
    {
        $modules = $modules->filter(fn (Module $module) => ! isset(self::$staticFieldCache[$module->id]))->values();

        if ($modules->isEmpty()) {
            return;
        }

        $moduleIds = $modules->pluck('id');

        $fields = Field::query()
            ->where(function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds)
                    ->orWhere('is_global', true);
            })
            ->select([
                'id',
                'module_id',
                'dropdown_list_id',
                'related_module',
                'name',
                'type',
                'key',
                'readonly',
                'sortable',
                'searchable',
                'filterable',
                'label',
                'required',
                'is_draft',
                'is_calculated',
                'is_global',
            ])
            ->with('dropdown_list')
            ->get();

        $globalFields = $fields->where('is_global', true);

        $lineItemFields = collect();
        if ($modules->contains(fn (Module $module) => $module->has_line_items)) {
            $lineItemFields = Field::query()
                ->where('is_default_for_line_items', true)
                ->select([
                    'id',
                    'module_id',
                    'dropdown_list_id',
                    'related_module',
                    'name',
                    'type',
                    'key',
                    'readonly',
                    'sortable',
                    'searchable',
                    'filterable',
                    'label',
                    'required',
                    'is_draft',
                ])
                ->get();
        }

        foreach ($modules as $module) {
            $moduleFields = $fields->where('module_id', $module->id)
                ->merge($globalFields)
                ->unique('id')
                ->values();

            if ($module->has_line_items) {
                $moduleFields = $moduleFields->merge($lineItemFields);
            }

            self::$staticFieldCache[$module->id] = $moduleFields;
        }
    }

    /**
     * @return Collection<Field>
     */
    public function allEditableFields(): Collection
    {
        $key = $this->id;

        if (! isset(self::$staticFieldCache[$key])) {
            self::$staticFieldCache[$key] = Field::query()
                ->where('module_id', $this->id)
                ->select([
                    'id',
                    'module_id',
                    'dropdown_list_id',
                    'related_module',
                    'name',
                    'type',
                    'key',
                    'readonly',
                    'sortable',
                    'searchable',
                    'label',
                    'required',
                    'is_draft',
                    'related_module',
                ])
                ->with('dropdown_list')
                ->get();
        }

        return self::$staticFieldCache[$key];
    }

    public function draftFields(): Collection
    {
        return Field::query()
            ->where('module_id', $this->id)
            ->where('is_draft', true)
            ->select([
                'id',
                'module_id',
                'dropdown_list_id',
                'related_module',
                'name',
                'type',
                'key',
                'readonly',
                'sortable',
                'searchable',
                'label',
                'required',
                'is_draft',
            ])
            ->with('dropdown_list')
            ->get();
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new AdminOnlyModuleScope);
    }

    /**
     * @return Collection<Field>
     */
    public function relatedfields()
    {
        return Field::query()
            ->where(function ($query) {
                $query->where('module_id', $this->id)
                    ->orWhere('is_global', true);
            })
            ->select([
                'id',
                'module_id',
                'dropdown_list_id',
                'related_module',
                'name',
                'type',
                'key',
                'readonly',
                'sortable',
                'label',
                'required',
            ])
            ->with('dropdown_list')
            ->get();
    }

    /**
     * Get fields for builder (DB + default fields, unique by key)
     */
    public function builderFields(): Collection
    {
        $dbFields = $this->allFields();

        $defaultFields = collect($this->getDefaultFields())->map(function ($field) {
            return new Field($field);
        });

        $lineItemFields = $this->has_line_items
        ? collect(config('default_line_item_fields'))->map(fn ($field) => new Field($field))
        : collect();

        return $defaultFields
            ->merge($dbFields)
            ->merge($lineItemFields)
            ->unique('name')
            ->values();
    }

    public function getFieldMetadata(string $field_name): array
    {
        $excluded = ['id', 'key', 'module_id', 'is_custom', 'is_active', 'is_draft', 'is_default', 'is_global', 'database_type', 'deleted_at', 'created_at', 'updated_at','is_default_for_line_items'];
        $field = Field::query()
            ->where(function ($query) {
                $query->where('module_id', $this->id)
                    ->orWhere('is_global', true);
            })
            ->where('name', $field_name)
            ->first();

        return array_diff_key(
            $field->getAttributes(),
            array_flip($excluded)
        );
    }

    protected function defaultReadonlyFor(string $key, string $type): bool
    {
        return in_array($key, ['created_at', 'updated_at'], true);
    }

    protected static array $staticLayoutCache = [];

    protected function resolveLayout(string $type): array
    {
        $key = $this->id . ':' . $type;

        if (isset(self::$staticLayoutCache[$key])) {
            return self::$staticLayoutCache[$key];
        }

        // 1. DB layout
        $layout = $this->layouts()->where('type', $type)->first();
        if ($layout !== null) {
            return self::$staticLayoutCache[$key] = Layout::normalize($layout->definition ?? []);
        }

        // 2. Module config fallback
        $moduleConfig = config("module_layouts.{$this->slug}");
        if (is_array($moduleConfig) && isset($moduleConfig[$type])) {
            return self::$staticLayoutCache[$key] = Layout::normalize($moduleConfig[$type]);
        }

        // 3. Global fallback
        $globalDefault = Layout::getDefaultLayout($type);
        if ($globalDefault !== null) {
            return self::$staticLayoutCache[$key] = Layout::normalize($globalDefault);
        }

        throw new \Exception("No {$type} layout found for module {$this->name}");
    }

    public function relationships(): Collection
    {
        return RelationshipService::getRelationshipForModule($this->slug);
    }

    /**
     * The module line items should snapshot/search from when has_line_items is true.
     * Falls back to 'products' as default.
     */
    public function lineItemSourceModuleSlug(): ?string
    {
        if (! $this->has_line_items) {
            return null;
        }

        return $this->line_item_source_module ?? 'products';
    }

    /**
     * The source module can only be (re)configured while no line items have been
     * created yet for this module — changing it afterward would silently orphan
     * existing rows' product_id references and invalidate any snapshot mapping.
     */
    public function canChangeLineItemSourceModule(): bool
    {
        return ! \App\Models\Modules\LineItem::where('parent_type', $this->slug)->exists();
    }

    /**
     * Instantiate the actual business model this registry entry describes.
     */
    public function getInstance(): BaseModule
    {
        return new ($this->class_name);
    }

    public function getDefaultFields(): array
    {
        return config('default_fields');
    }
}
