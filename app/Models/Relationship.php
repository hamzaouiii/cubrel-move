<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Relationship extends Model
{
  protected $table = 'relationships';
  use HasUuids;
  protected $guarded = [];
  protected $fillable = [
    'type',
    'right_module',
    'name',
    'label',
    'left_module',
    'is_system',
    'join_table',
    'right_class',
    'left_class',

  ];
  protected $excludedFromForms = [
    'is_system',
    'join_table',
    'right_class',
    'left_class',
    'left_module',


  ];
  public function links(): HasMany
  {
    return $this->hasMany(RelationshipLink::class);
  }

  public function leftModule()
  {
    return $this->belongsTo(Module::class, 'left_module', 'slug');
  }

  public function rightModule()
  {
    return $this->belongsTo(Module::class, 'right_module', 'slug');
  }
  public function getEmptyMetadata()
  {
    return array_diff($this->fillable, $this->excludedFromForms);
  }
}
