<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Relationship extends Model
{
  protected $table = 'relationships';

  protected $guarded = [];

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
}
