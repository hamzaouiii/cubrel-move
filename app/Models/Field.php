<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\DropdownList;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Field extends Model
{
  use HasFactory, HasUuids;

  protected $table = 'fields';

  /**
   * IDs are UUIDs (char 36)
   */
  public $incrementing = false;
  protected $keyType = 'string';

  /**
   * Mass assignable attributes
   */
  protected $fillable = [
    'id',
    'module_id',
    'type',
    'related_module',
    'label',
    'name',
    'key',
    'is_custom',
    'is_active',
    'is_draft',
    'is_calculated',
    'readonly',
    'hidden',
    'required',
    'searchable',
    'filterable',
    'sortable',
    'database_type',
    'options',
    'default_value',
    'min_length',
    'max_length',
    'regex',
    'dropdown_list_id',
  ];

  protected $excludedFromForms = [
    'id',
    'module_id',
    'key',
    'is_custom',
    'is_draft',
    'is_default',
    'regex',
    'is_active',
    'database_type',
    'dropdown_list_id',
    'is_default_for_line_items',
    'is_calculated',

  ];
  /**
   * Attribute casting
   */
  protected $casts = [
    'is_custom'   => 'boolean',
    'is_active'   => 'boolean',
    'is_draft'   => 'boolean',
    'is_global'   => 'boolean',
    'is_calculated' => 'boolean',
    'readonly'    => 'boolean',
    'hidden'      => 'boolean',
    'required'    => 'boolean',
    'searchable'  => 'boolean',
    'filterable'  => 'boolean',
    'sortable'    => 'boolean',
    'options'     => 'array',
    'min_length'  => 'integer',
    'max_length'  => 'integer',
  ];


  public function module()
  {
    return $this->belongsTo(Module::class);
  }

  public function isRequired(): bool
  {
    return $this->required && ! $this->nullable;
  }

  public function isVisible(): bool
  {
    return ! $this->hidden && $this->is_active;
  }

  public function hasOptions(): bool
  {
    return ! empty($this->options);
  }
  public function getEmptyMetadata(): array
  {
    return array_diff($this->fillable, $this->excludedFromForms);
  }

  public function dropdown_list(): HasOne
  {
    return $this->hasOne(DropdownList::class, 'id', 'dropdown_list_id');
  }
}
