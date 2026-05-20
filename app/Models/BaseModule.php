<?php

namespace App\Models;

use App\Concerns\HasCustomFields;
use App\Concerns\HasDynamicRelationships;
use App\Concerns\HasTranslatableLabel;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * Summary of BaseModule
 * This is a Business Class
 * A sibling class to App/Models/Module
 * All Modules Extend BaseModule By default
 */
abstract class BaseModule extends Model
{
    use HasCustomFields;
    use HasDynamicRelationships;
    use HasFactory;
    use HasTranslatableLabel;
    use HasUuids;
    use Searchable;

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Child can define $moduleCasts property instead
        // used in Deals and Cases for example.
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

  public function toSearchableArray(): array
{
    return collect($this->searchableFields())
        ->mapWithKeys(fn(string $field) => [$field => $this->$field])
        ->toArray();
}

    public function uniqueIds()
    {
        return ['id'];
    }

    protected static array $moduleSlugCache = [];

    /**
     * Creates a link between two records
     * @param string $relationship_name
     * @param string $related_id
     * @return void
     */
    public function link(string $relationship_name, string $related_id): void
    {
        RelationshipService::link($relationship_name, static::getModuleSlug(), $this->id, $related_id);
    }

    /**
     * deletes relationship bwtween two records
     * @param string $relationship_name
     * @param string $related_id
     * @return void
     */
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

    /**
     * returns current module slug
     * @return string
     */
    public static function getModuleSlug(): string
    {
        return static::$moduleSlugCache[static::class] ??=
          Module::where('model_class', static::class)->value('slug');
    }

    public static function getDefaultOwnerId(): string
    {
        // 1. Return the authenticated user if they exist
        if (auth()->check()) {
            return auth()->id();
        }

        // 2. Fallback: Return the admin user
        // 3. Last Resort: Use a the first user found in DB
        return User::query()->where('username', 'admin')->first() ?? User::first()?->id;
    }

    /**
     * returns searchable field definitions for this module 
     * @return array
     */
    protected function searchableFields(): array
    {   $module = $this->moduleDefinition();
        $dbFields = $module->allFields()
            ->where('searchable', true)
            ->pluck('name')
            ->toArray();

        return array_unique($dbFields);
    }
    
    /**
     * For Laravel/scout, defines what is to be returned as a result for search
     * @return array{id: mixed, label: mixed, module: string, sublabel: \Illuminate\Support\Stringable|null, url: string}
     */
    public function toSearchResult(): array
    {
        return [
            'id' => $this->id,
            'module' => $this->getModuleSlug(),
            'label' => $this->name,
            'sublabel' => $this->description
                ? str($this->description)->limit(60)
                : null,
            'url' => $this->getModuleSlug().'/'.$this->id,
        ];
    }
}
