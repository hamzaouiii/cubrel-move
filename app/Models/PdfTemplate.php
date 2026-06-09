<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    protected $fillable = [
        'module_slug',
        'name',
        'blade_view',
        'layout_id',
        'description',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public static function defaultFor(string $moduleSlug): ?self
    {
        return static::where('module_slug', $moduleSlug)
            ->where('is_default', true)
            ->first();
    }

    public static function existsFor(string $moduleSlug): bool
    {
        return static::where('module_slug', $moduleSlug)
            ->where('is_default', true)
            ->exists();
    }
}
