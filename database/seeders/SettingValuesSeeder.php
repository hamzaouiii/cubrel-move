<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingValuesSeeder extends Seeder
{
    public function run(): void
    {
        self::seedDefaultSettings();
        self::seedNotificationDefaults();
        self::seedDataRetentionDefaults();
    }

    public static function seedDefaultSettings(): void
    {
        foreach (config('default_settings') as $row) {
            if (DB::table('setting_values')->where('key', $row['key'])->exists()) {
                continue;
            }

            DB::table('setting_values')->insert([
                'id' => (string) Str::uuid(),
                'setting_item' => $row['setting_item'],
                'key' => $row['key'],
                'value' => $row['value'],
                'label' => $row['label'],
                'type' => $row['type'],
                'sort_order' => $row['sort_order'],
                'autoload' => $row['autoload'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }


    public static function seedDataRetentionDefaults(): void
    {
        $rows = array_filter(
            config('default_settings'),
            fn (array $row) => $row['setting_item'] === 'data-retention'
        );

        foreach ($rows as $row) {
            if (DB::table('setting_values')->where('key', $row['key'])->exists()) {
                continue;
            }

            DB::table('setting_values')->insert([
                'id' => (string) Str::uuid(),
                'setting_item' => $row['setting_item'],
                'key' => $row['key'],
                'value' => $row['value'],
                'label' => $row['label'],
                'type' => $row['type'],
                'sort_order' => $row['sort_order'],
                'autoload' => $row['autoload'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public static function seedNotificationDefaults(): void
    {
        $sortOrder = 0;
        foreach (config('default_notification_settings') as $key => $value) {
            $sortOrder++;

            if (DB::table('setting_values')->where('key', $key)->exists()) {
                continue;
            }

            DB::table('setting_values')->insert([
                'id' => (string) Str::uuid(),
                'setting_item' => 'notifications',
                'key' => $key,
                'value' => $value,
                'label' => "settings.fields.{$key}",
                'type' => 'bool',
                'sort_order' => $sortOrder,
                'autoload' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
