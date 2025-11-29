<?php

namespace App\Models\Settings;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class SettingValue extends Model
{
    use HasFactory;

    protected $table = 'setting_values';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'setting_item_id',
        'key',
        'value',
        'type',
        'label',
        'autoload',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function settingItem()
    {
        return $this->belongsTo(SettingItem::class);
    }

    /* Optional casting logic */
    public function getCastedValueAttribute()
    {
        return match ($this->type) {
            'int', 'integer' => (int) $this->value,
            'bool', 'boolean' => in_array(strtolower($this->value), ['1','true','yes']),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
