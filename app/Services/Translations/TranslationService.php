<?php

namespace App\Services\Translations;

use Illuminate\Support\Facades\Cache;
use App\Models\Label;

class TranslationService
{
  public static function all(): array
  {
    $resolver = function () {

      $dbLabels = Label::pluck('value', 'key')
        ->map(fn($value) => json_decode($value, true))
        ->toArray();

      $groups = [
        'settings',
        'modules',
        'pagination',
        'sidebar',
        'topbar',
        'layouts',
        'fields',
        'globals',
        'dropdowns',
        'calendar',
        'relationships'
      ];

      $translations = [];

      foreach ($groups as $group) {

        $default = __($group);

        $translations[$group] = array_replace_recursive(
          $default,
          $dbLabels[$group] ?? []
        );
      }

      $translations['custom'] = $dbLabels;

      return $translations;
    };

    if (!app()->environment('local')) {
      return Cache::rememberForever('translations.all', $resolver);
    }

    return $resolver();
  }
}
