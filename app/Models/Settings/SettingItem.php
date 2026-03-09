<?php

namespace App\Models\Settings;

use App\Concerns\HasTranslatableLabel;
use App\Models\Settings\SettingValue;

final class SettingItem
{
  use  HasTranslatableLabel;


  protected $table = null;

  public static function all()
  {
    $configItems = config('settings', []);
    $items = [];

    foreach ($configItems as $group) {
      foreach ($group['items'] as $item) {
        $items[] = new static([
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
    return SettingValue::where('setting_item', $this->slug)
      ->where('autoload', true)
      ->get();
  }
}
