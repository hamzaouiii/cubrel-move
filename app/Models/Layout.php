<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Layout extends Model
{
  use HasUuids;

  protected $fillable = [
    'module_name',
    'type',
    'name',
    'definition',
    'is_default',
  ];

  protected $casts = [
    'definition' => 'array',
    'is_default' => 'boolean',
  ];

  public function module()
  {
    return $this->belongsTo(Module::class);
  }


  public function scopeForType($query, string $type)
  {
    return $query->where('type', $type);
  }

  public function scopeDefault($query)
  {
    return $query->where('is_default', true);
  }

  public static function getDefaultLayout(string $type)
  {
    $layout = config("default_layouts.{$type}");
    return $layout;
  }

  public static function getGlobalListLayout()
  {
    return config("default_layouts.list", []);
  }

  public static function getGlobalRecordLayout()
  {
    return config("default_layouts.record", []);
  }
}
