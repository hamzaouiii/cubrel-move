<?php

namespace App\Support;

use App\Models\Settings\SettingValue;
use Illuminate\Support\Facades\Cache;

class Settings
{
  protected const CACHE_KEY = 'settings.all';
  protected static array $cache = [];

  protected static function load(): array
  {
    if (!empty(static::$cache)) {
      return static::$cache;
    }

    $resolver = fn() => SettingValue::pluck('value', 'key')->toArray();

    static::$cache = app()->environment('production')
      ? Cache::rememberForever(self::CACHE_KEY, $resolver)
      : $resolver();

    return static::$cache;
  }

  public static function get(string $key, $default = null)
  {
    return static::load()[$key] ?? $default;
  }

  public static function set(string $key, $value): void
  {
    $record = SettingValue::firstOrNew(['key' => $key]);
    $record->value = $value;
    $record->save();

    static::clearCache();
  }

  public static function bool(string $key, bool $default = false): bool
  {
    $value = static::get($key);
    if ($value === null) {
      return $default;
    }

    return (bool) (int) $value;
  }

  public static function locale(): string
  {
    return static::get('app_locale', config('app.locale'));
  }

  public static function all(): array
  {
    return static::load();
  }

  public static function clearCache(): void
  {
    Cache::forget(self::CACHE_KEY);
    static::$cache = [];
  }
}
