<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Layout;

class Module extends BaseModule
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
        'show_in_sidebar',
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

    public function layouts()
    {
        return $this->hasMany(Layout::class);
    }

    public function listLayout()
    {
        return $this->layouts()
            ->where('type', 'list')
            ->first();
    }

    public function recordLayout()
    {
        $recordLayout = $this->layouts()
            ->where('type', 'record')
            ->first();
        if(!empty($recordLayout)){
          return $recordLayout;
        }
        else{
          return Layout::getDefaultLayout('record');
        }
    }

    // optional generic helper
    public function layoutFor(string $type)
    {
        return $this->layouts()
            ->where('type', $type)
            ->first();
    }


} 