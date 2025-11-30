<?php
namespace App\Support;

use App\Models\Settings\SettingValue;
use Illuminate\Support\Facades\Cache;

class Settings
{
    protected static array $cache = [];

    public static function get(string $key, $default = null) {
        static::$cache =  SettingValue::where('autoload', true)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        return static::$cache[$key] ?? $default;

    }

    public static function set(string $key, $value): void{
        $record = SettingValue::firstOrNew(['key' => $key]);
        $record->value = $value;
        $record->save();

        Cache::forget('app_settings');
        static::$cache = [];
    }
    public static function bool(string $key, bool $default = false): bool{
        $value = static::get($key);
        if ($value === null) {
            return $default;
        }

        return (bool) (int) $value;
    }
    public static function locale(): string{
      return static::get('app_locale', config('app.locale'));
    }

    public static function all(): array
    {
      return SettingValue::where('autoload', true)
      ->get()
      ->pluck('value', 'key')
      ->toArray();
    }



}
