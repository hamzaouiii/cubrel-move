<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Concerns\HasCustomFields;
use App\Models\Layout;
use App\Models\Field;

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
class Module extends model
{
  use HasUuids;
  use HasTranslatableLabel;
  use HasCustomFields;

  protected $fillable = [
    'slug',
    'name',
    'icon',
    'label',
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

  /**
   * @return array
   */
  public function listLayout()
  {
    /** @var Layout|null $layout */
    $layout = $this->layouts()->where('type', 'list')->first();
    if ($layout !== null) {
      return $layout->definition;
    }

    $globalDefault = Layout::getDefaultLayout('list');
    if ($globalDefault !== null) {
      return $globalDefault;
    }
    throw new \Exception("No list layout found for module {$this->name} and no global fallback available.");
  }


  /**
   * @return array
   */
  public function recordLayout()
  {
    /** @var Layout|null $recordLayout */

    $recordLayout = $this->layouts()->where('type', 'record')->first();

    if ($recordLayout !== null) {
      return $recordLayout->definition;
    }
    $globalDefault = Layout::getDefaultLayout('record');
    if ($globalDefault !== null) {
      return $globalDefault;
    }
    throw new \Exception("No record layout found for module {$this->name} and no global fallback available.");
  }

  // public function relationships(): array
  // {
  //   // return RelationshipRegistry::forModule($this->slug);
  // }

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
        'label',
        'required',
      ])
      ->with('dropdown_list');
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
}
