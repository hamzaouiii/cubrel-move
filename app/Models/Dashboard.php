<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dashboard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'layout',
        'sort_order',
    ];

    protected $casts = [
        'layout' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The global default dashboard (no user_id).
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('owner_id');
    }

    /**
     * Dashboards belonging to a specific user or the global default.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('owner_id', $userId)
              ->orWhereNull('owner_id');
        });
    }
}