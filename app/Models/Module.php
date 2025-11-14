<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'label',
        'icon',
        'color',
        'path',
        'sort_order',
        'is_active',
        'description',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
        'model_class',
        'table_name',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'can_view'   => 'boolean',
        'can_create' => 'boolean',
        'can_edit'   => 'boolean',
        'can_delete' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
