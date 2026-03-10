<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Models\Layout;
use App\Models\Field;
use Illuminate\Support\Collection;
use App\Services\Relationships\RelationshipService;

/**
 * This is an infrastructure class. A Module is an editable item that contains metadata for each module. 
 * Not to be confused with BaseModule => app\Models\BaseModule.php which is a business module, all CRM modules are to extend it, unlike this one which probably needs to be an abstract class
 */
/**
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Layout> $layouts
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Field> $fields
 * @method \Illuminate\Database\Eloquent\Relations\HasMany layouts()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany fields()
 */
class Module extends Model
{
  use HasUuids;
  use HasTranslatableLabel;

  protected $fillable = [
    'slug',
    'name',
    'icon',
    'label',
    'single_label',
    'color',
    'path',
    'sort_order',
    'is_active',
    'description',
    'can_view',
    'can_create',
    'can_edit',
    'can_delete',
    'model_class',
    'handler_class',
    'table_name',
    'show_in_sidebar',
    'is_custom'
  ];

  protected $casts = [
    'is_active'  => 'boolean',
    'can_view'   => 'boolean',
    'can_create' => 'boolean',
    'can_edit'   => 'boolean',
    'can_delete' => 'boolean',
  ];
  protected $guarded = [];
  public $timestamps = true;
  public static function forSidebar(): Collection
  {
    return self::active()
      ->where('show_in_sidebar', 1)
      ->orderBy('id')
      ->get()
      ->map(function (Module $module) {

        return [
          'slug'  => $module->slug,
          'name' => $module->name,
          'icon'  => $module->icon,
          'color' => $module->color,
          'path'  => $module->path,
          'label' => $module->label,
          'single_label' => $module->single_label
        ];
      })
      ->values();
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  /**
   * @return HasMany<Layout, $this>
   */
  public function layouts()
  {
    return $this->hasMany(Layout::class);
  }

  public function getDefaultLayout(String $type)
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

  public function getDataForPanel(): array
  {
    return [
      'linkingPanel' => $this->linkingPanelLayout(),
      'fields' => $this->fields
    ];
  }

  public function layoutFor(string $type)
  {
    return $this->layouts()
      ->where('type', $type)
      ->first();
  }

  /**
   * @return HasMany<Field, $this>
   */
  public function fields()
  {
    return $this->hasMany(Field::class, 'module_id', 'id')
      ->select([
        'id',
        'module_id',
        'dropdown_list_id',
        'name',
        'type',
        'key',
        'readonly',
        'sortable',
        'searchable',
        'label',
        'required',
      ])
      ->with('dropdown_list');
  }

  public function relatedfields()
  {
    return $this->hasMany(Field::class, 'module_id', 'id')
      ->select([
        'id',
        'module_id',
        'dropdown_list_id',
        'name',
        'type',
        'key',
        'readonly',
        'sortable',
        'label',
        'required',
      ])->with('dropdown_list');
  }
  public function getFieldMetadata(string $field): array
  {
    $excluded = ['id', 'key', 'module_id', 'is_custom', 'is_active', 'database_type', 'deleted_at', 'created_at', 'updated_at'];
    $field = $this->hasMany(Field::class, 'module_id', 'id')
      ->firstWhere('name', $field);
    return array_diff_key(
      $field->getAttributes(),
      array_flip($excluded)
    );
  }

  protected function defaultReadonlyFor(string $key, string $type): bool
  {
    return in_array($key, ['created_at', 'updated_at'], true);
  }

  protected function resolveLayout(string $type): array
  {
    // 1. DB layout
    $layout = $this->layouts()->where('type', $type)->first();
    if ($layout !== null) {
      return Layout::normalize($layout->definition ?? []);
    }

    // 2. Module config fallback
    $moduleConfig = config("module_layouts.{$this->slug}");
    if (is_array($moduleConfig) && isset($moduleConfig[$type])) {
      return Layout::normalize($moduleConfig[$type]);
    }

    // 3. Global fallback
    $globalDefault = Layout::getDefaultLayout($type);
    if ($globalDefault !== null) {
      return Layout::normalize($globalDefault);
    }

    throw new \Exception("No {$type} layout found for module {$this->name}");
  }

  public function relationships(): Collection
  {
    return RelationshipService::getRelationshipForModule($this->model_class);
  }
}
