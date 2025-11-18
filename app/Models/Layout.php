<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layout extends Model
{
    protected $fillable = [
        'module_id',
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

    // Small helper scopes

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}