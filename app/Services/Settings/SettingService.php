<?php

namespace App\Services\Settings;

use Illuminate\Support\Facades\Cache;
use App\Models\Settings\SettingValue;

class SettingService
{
  protected const CACHE_KEY = 'settings.all';

  public static function all(): array
  {
    $resolver = function () {
      // Adjust the pluck based on your DB structure (e.g., key => value)
      return SettingValue::pluck('value', 'key')->toArray();
    };

    if (app()->environment('production')) {
      return Cache::rememberForever(self::CACHE_KEY, $resolver);
    }

    return $resolver();
  }

  public static function clearCache(): void
  {
    Cache::forget(self::CACHE_KEY);
  }
}
