<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Layout;

class Module extends BaseModule
{
    protected $fillable = [
        'slug',
        'name',
        'icon',
        'label',
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
        'handler_class',
        'table_name',
        'show_in_sidebar',
        'is_custom'
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
        $layout = $this->layouts()
            ->where('type', 'list')
            ->first();

        if ($layout) {
            return $layout;
        }

        $globalDefault = \App\Models\Layout::where('type', 'list')
            ->where('module_name', 'global')
            ->where('is_list_default', 1)
            ->first();

        if ($globalDefault) {
            return $globalDefault;
        }

        $globalFallback = \App\Models\Layout::where('type', 'list')
            ->where('module_name', 'global')
            ->first();

        if ($globalFallback) {
            return $globalFallback;
        }

        throw new \Exception("No list layout found for module {$this->name} and no global fallback available.");
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

    public function layoutFor(string $type)
    {
        return $this->layouts()
            ->where('type', $type)
            ->first();
    }


} 