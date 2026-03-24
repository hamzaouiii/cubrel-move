<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Concerns\HasCustomFields;
use App\Concerns\HasDynamicRelationships;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Factories\HasFactory;


abstract class BaseModule extends Model
{
  use HasUuids;
  use HasTranslatableLabel;
  use HasCustomFields;
  use HasFactory;
  use HasDynamicRelationships;
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
  protected static array $moduleSlugCache = [];
  public function link(string $relationship_name, string $related_id): void
  {
    RelationshipService::link($relationship_name, static::getModuleSlug(), $this->id, $related_id);
  }

  public function unlinkRelation(string $relationship_name, string $related_id): void
  {
    RelationshipService::unlink($relationship_name, static::getModuleSlug(), $this->id, $related_id);
  }

  /**
   * Retrieve the Module registry record that describes this model.
   */
  public function moduleDefinition(): Module
  {
    return Module::where('model_class', static::class)->firstOrFail();
  }
  public static function getModuleSlug(): string
  {
    return static::$moduleSlugCache[static::class] ??=
      Module::where('model_class', static::class)->value('slug');
  }
}
