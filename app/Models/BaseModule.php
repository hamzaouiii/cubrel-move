<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Concerns\HasTranslatableLabel;
use App\Concerns\HasCustomFields;
use App\Concerns\HasDynamicRelationships;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

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

  public function __construct(array $attributes = []){
    parent::__construct($attributes);
    
    // Child can define $moduleCasts property instead
    if (property_exists($this, 'moduleCasts')) {
        $this->casts = array_merge($this->casts, $this->moduleCasts);
    }
  }

  protected static function booted(): void
  {
    static::creating(function (self $model) {
      // If no owner_id was provided in the request/seeder payload, fall back to the default
      if (empty($model->owner_id)) {
        $model->owner_id = static::getDefaultOwnerId();
      }
    });
  }

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

  public static function getDefaultOwnerId(): String
  {
    // 1. Return the authenticated user if they exist
    if (auth()->check()) {
      return auth()->id();
    }

    // 2. Fallback: Return the first User in the DB (ideal for seeders)
    // 3. Last Resort: Use a constant or throw an exception if DB is empty
    return User::first()?->id ?? 1;
  }
}
