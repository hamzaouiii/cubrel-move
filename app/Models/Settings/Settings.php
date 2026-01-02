<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Concerns\HasTranslatableLabel;

class Settings extends Model
{
  use HasFactory, HasTranslatableLabel;

  /**
   * Use UUIDs instead of auto-incrementing integers.
   */
  public $incrementing = false;

  /**
   * The "type" of the primary key ID.
   *
   * @var string
   */
  protected $keyType = 'string';

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    // TODO: add your module fields here
  ];

  protected static function booted(): void
  {
    static::creating(function (self $model) {
      if (! $model->getKey()) {
        $model->setAttribute($model->getKeyName(), (string) Str::uuid());
      }
    });
  }
  public function items()
  {
    return $this->hasMany(SettingItem::class, 'setting_id')
      ->where('active', true);
  }
}
