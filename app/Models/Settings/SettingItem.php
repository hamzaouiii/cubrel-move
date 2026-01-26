<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use App\Concerns\HasTranslatableLabel;
use Illuminate\Support\Str;

final class SettingItem extends Model
{
  use  HasTranslatableLabel;


  protected $table = null;

  public static function all($columns = ['*'])
  {
    $configItems = config('settings', []);
    $items = [];

    foreach ($configItems as $group) {
      foreach ($group['items'] as $item) {
        $items[] = new static([
          'id' => Str::uuid()->toString(),
          'name' => $item['name'],
          'slug' => $item['slug'],
          'label' => $item['label'],
          'path' => $item['path'],
          'icon' => $item['icon'],
          'category' => $group['name'],
        ]);
      }
    }

    return collect($items);
  }
  public function values()
  {
    return $this->hasMany(SettingValue::class,  'setting_item', 'slug')
      ->where('autoload', true);
  }
}
