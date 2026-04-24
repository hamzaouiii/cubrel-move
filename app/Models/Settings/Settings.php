<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use App\Concerns\HasTranslatableLabel;

class Settings extends Model
{
  use HasTranslatableLabel;

  protected static  $settings;


  public static function all($columns = ['*'])
  {
    return config("settings");
  }
  public static function allActive()
  {
    $settings = config("settings");

    $activeSettings = array_map(
      fn($group) => array_merge(
        $group,
        ['items' => array_filter($group['items'], fn($item) => $item['isActive'])]
      ),
      $settings
    );
    return array_filter($activeSettings, fn($group) => !empty($group['items']));;
  }
  public static function getItem($categorty, $slug)
  {
    $settings = config("settings");
    if (isset($settings[$categorty]['items'][$slug])) {
      return $settings[$categorty]['items'][$slug];
    }
    abort(404);
  }
}
