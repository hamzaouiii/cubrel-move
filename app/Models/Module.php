<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Layout;
use App\Models\Field;
use App\Models\DropdownList;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Layout> $layouts
 * @method \Illuminate\Database\Eloquent\Relations\HasMany layouts()
 */
class Module extends BaseModule
{
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

  public function layouts()
  {
    return $this->hasMany(Layout::class);
  }

  public function listLayout()
  {
    $layout = $this->layouts()->where('type', 'list')->first();
    if ($layout) {
      return $layout->definition;
    }

    $globalDefault = Layout::getDefaultLayout('list');
    if ($globalDefault) {
      return $globalDefault;
    }

    throw new \Exception("No list layout found for module {$this->name} and no global fallback available.");
  }

  public function recordLayout()
  {
    $recordLayout = $this->layouts()->where('type', 'record')->first();

    if ($recordLayout) {
      return $recordLayout->definition;
    }
    $globalDefault = Layout::getDefaultLayout('record');
    if ($globalDefault) {
      return $globalDefault;
    }
    return $recordLayout;
  }

  public function dropdownLists()
  {
    return $this->hasMany(DropdownList::class, 'module_id', 'id');
  }

  public function layoutFor(string $type)
  {
    return $this->layouts()
      ->where('type', $type)
      ->first();
  }

  public function fields()
  {
    return $this->hasMany(Field::class, 'module_id', 'id')
      ->select([
        'name',
        'type',
        'key',
        'readonly',
        'sortable',
        'label',
        'required'
      ]);
  }

  public function getFieldMetadata(string $field)
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
