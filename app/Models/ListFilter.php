<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ListFilter extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_slug',
        'slug',
        'name',
        'label',
        'user_id',
        'is_shared',
        'is_system',
        'is_global',
        'conditions',
        'match_type',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_system' => 'boolean',
        'is_global' => 'boolean',
        'conditions' => 'array',
    ];

    /**
     * Filters that apply to a module: ones scoped directly to it, plus global ones.
     */
    public function scopeForModule(Builder $query, string $moduleSlug): Builder
    {
        return $query->where(function (Builder $q) use ($moduleSlug) {
            $q->where('module_slug', $moduleSlug)->orWhere('is_global', true);
        });
    }

    /**
     * Filters a user is allowed to see/apply: their own, anyone's shared, or system filters.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('is_shared', true)
                ->orWhere('is_system', true);
        });
    }

    public function canManage(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return ! $this->is_system && $this->user_id === $user->id;
    }

    /**
     * Resolve a filter key (slug or uuid) the given user is allowed to use, scoped to a module.
     */
    public static function findVisibleByKey(string $moduleSlug, string $key, User $user): ?self
    {
        $query = static::query()->forModule($moduleSlug)->visibleTo($user);

        return Str::isUuid($key)
            ? $query->where('id', $key)->first()
            : $query->where('slug', $key)->first();
    }
}
