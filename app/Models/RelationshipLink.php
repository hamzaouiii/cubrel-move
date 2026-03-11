<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RelationshipLink extends Model
{
  use HasUuids;
  protected $type = '';
  protected $fillable = [
    'id',
    'relationship_id',
    'left_id',
    'right_id'
  ];

  public static function booted()
  {
    static::creating(function ($model) {
      // Route to different tables based on type
      $model->setTable(self::getTableForRelationship(
        $model->type
      ));
    });

    static::retrieved(function ($model) {
      // Dynamically set table for queries
      $model->setTable(self::getTableForRelationship(
        $model->type
      ));
    });
  }
  private static function getTableForRelationship($type)
  {
    return match ($type) {
      // map existing pivots here later
      default => 'relationship_links'
    };
  }
}
