<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Concerns\HasCustomFields;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Factories\HasFactory;


abstract class BaseModule extends Model
{
  use HasUuids;
  use HasTranslatableLabel;
  use HasCustomFields;
  use HasFactory;
  protected $casts = [
    'custom_fields' => 'array',
  ];
  public $incrementing = false;
  protected $keyType = 'string';
  protected $guarded = [];
  public $timestamps = true;
  public function uniqueIds()
  {
    return ['id'];
  }

  public function link(string $relationship_name, string $related_id): void
  {
    RelationshipService::link($relationship_name, static::class, $this->id, $related_id);
  }

  public function unlinkRelation(string $relationship_name, string $related_id): void
  {
    RelationshipService::unlink($relationship_name, static::class, $this->id, $related_id);
  }
}
