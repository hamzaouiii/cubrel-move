<?php

namespace App\Models\Settings;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use App\Concerns\HasTranslatableLabel;
use App\Services\Settings\SettingsService;


class SettingValue extends Model
{
  use HasFactory, HasUuids, HasTranslatableLabel;

  protected $table = 'setting_values';

  public $incrementing = false;
  protected $keyType = 'string';

  protected $fillable = [
    'setting_item',
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
}
