<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Settings\SettingItem;
use App\Models\Settings\SettingValue;

class SettingValuesTableSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            '/settings/system/style' => [
                ['key' => 'theme', 'value' => 'light', 'type' => 'string'],
                ['key' => 'primary_color', 'value' => '#2ecc71', 'type' => 'string'],
                ['key' => 'secondary_color', 'value' => '#3498db', 'type' => 'string'],
                ['key' => 'border_radius', 'value' => '10', 'type' => 'int'],
                ['key' => 'table_striped_rows', 'value' => '1', 'type' => 'bool'],
            ],

            '/settings/system/locale' => [
                ['key' => 'default_locale', 'value' => 'de_DE', 'type' => 'string'],
                ['key' => 'fallback_locale', 'value' => 'en_US', 'type' => 'string'],
                ['key' => 'timezone', 'value' => 'Europe/Berlin', 'type' => 'string'],
                ['key' => 'first_day_of_week', 'value' => '1', 'type' => 'int'], // 1 = Monday
                ['key' => 'date_format', 'value' => 'd.m.Y', 'type' => 'string'],
            ],

            // use the users list page as container for user-related settings
            '/settings/users/list' => [
                ['key' => 'default_user_role', 'value' => 'standard_user', 'type' => 'string'],
                ['key' => 'allow_user_self_registration', 'value' => '0', 'type' => 'bool'],
                ['key' => 'require_email_verification', 'value' => '1', 'type' => 'bool'],
                ['key' => 'password_min_length', 'value' => '10', 'type' => 'int'],
                ['key' => 'lockout_after_failed_logins', 'value' => '5', 'type' => 'int'],
            ],

            '/settings/system/languages' => [
                ['key' => 'default_language', 'value' => 'de', 'type' => 'string'],
                ['key' => 'enabled_languages', 'value' => '["de","en"]', 'type' => 'json'],
                ['key' => 'show_language_switcher', 'value' => '1', 'type' => 'bool'],
                ['key' => 'fallback_language', 'value' => 'en', 'type' => 'string'],
            ],
        ];

        foreach ($groups as $path => $settings) {
            $item = SettingItem::where('path', $path)->first();

            if (! $item) {
                continue;
            }

            foreach ($settings as $setting) {
                SettingValue::updateOrCreate(
                    [
                        'setting_item_id' => $item->id,
                        'key' => $setting['key'],
                    ],
                    [
                        'value' => $setting['value'],
                        'type' => $setting['type'] ?? 'string',
                        'autoload' => true,
                    ]
                );
            }
        }
    }
}
