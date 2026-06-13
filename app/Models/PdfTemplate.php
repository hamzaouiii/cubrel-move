<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'module_slug',
        'name',
        'blade_view',
        'layout_id',
        'description',
        'is_default',
        'definition',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'definition' => 'array',
    ];

    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }

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
