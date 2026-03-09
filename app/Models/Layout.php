<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property array|null $definition
 */
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
    return self::normalize(config("default_layouts.{$type}", []));
  }

  public static function getGlobalListLayout()
  {
    return self::normalize(config("default_layouts.list", []));
  }

  public static function getGlobalRecordLayout()
  {
    return self::normalize(config("default_layouts.record", []));
  }

  public static function normalize(mixed $value): mixed
  {
    if (!is_array($value)) {
      return $value;
    }

    // Recursively normalize children first
    foreach ($value as $key => $child) {
      $value[$key] = self::normalize($child);
    }

    // If array has only numeric keys, reindex it
    if (self::isSequentialArray($value)) {
      return array_values($value);
    }

    return $value;
  }

  protected static function isSequentialArray(array $array): bool
  {
    if ($array === []) {
      return true;
    }

    return array_keys($array) === range(0, count($array) - 1);
  }
}
