<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Layout extends Model
{
    use HasUuids;

    protected $fillable = [
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

    // public function getDefaultLayout(string $type){
    //   return $this->where('type', $type)
    //   ->where('module_name', 'global')
    //   ->first();
    // }
    public static function getDefaultLayout(string $type)
    {
        return self::whereNull('module_id')
            ->where('type', $type)
            ->where(function ($q) use ($type) {
                return $type === 'record'
                    ? $q->where('is_record_default', true)
                    : $q->where('is_list_default', true);
            })
            ->first();
    }

  }