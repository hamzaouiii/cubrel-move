<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SettingItem extends Model
{
    use HasFactory;

    public $incrementing = false;     // UUID
    protected $keyType = 'string';    // UUID

    protected $fillable = [
        'id',
        'setting_id',
        'name',
        'path',
        'icon',
        'category',
    ];

    public function setting()
    {
        return $this->belongsTo(\App\Models\Modules\Settings::class, 'setting_id');
    }
    public function values()
    {
        return $this->hasMany(SettingValue::class)->where('autoload', true);
    }
}
