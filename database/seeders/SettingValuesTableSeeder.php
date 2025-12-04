<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingValuesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('setting_values')->delete();
        
        \DB::table('setting_values')->insert(array (
            0 => 
            array (
                'id' => '019acf24-a342-7167-8a45-dc6c98fc1d0c',
                'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'key' => 'app_locale',
                'value' => 'de',
                'label' => 'settings.fields.app_locale',
                'type' => 'lang_switcher',
                'autoload' => 1,
                'created_at' => '2025-11-29 10:24:36',
                'updated_at' => '2025-12-02 10:05:58',
            ),
            1 => 
            array (
                'id' => '07811a5e-c0bd-48c3-a77c-e9f7f7aacf17',
                'setting_item_id' => 'e6f6cde1-fe51-400a-8710-da218767d42f',
                'key' => 'show_language_switcher',
                'value' => '1',
                'label' => 'settings.fields.show_language_switcher',
                'type' => 'bool',
                'autoload' => 0,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            2 => 
            array (
                'id' => '209ad56f-3578-4905-9cf5-40dd4ed318cb',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'border_radius',
                'value' => '10',
                'label' => 'settings.fields.border_radius',
                'type' => 'int',
                'autoload' => 0,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            3 => 
            array (
                'id' => '306aec6e-02aa-469c-b124-0ddced48776b',
                'setting_item_id' => 'e6f6cde1-fe51-400a-8710-da218767d42f',
                'key' => 'enabled_languages',
                'value' => '["de","en"]',
                'label' => 'settings.fields.enabled_languages',
                'type' => 'json',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            4 => 
            array (
                'id' => '467d7f93-0172-47eb-b722-d0e34506f146',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'secondary_color',
                'value' => '#3498db',
                'label' => 'settings.fields.secondary_color',
                'type' => 'color',
                'autoload' => 0,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            5 => 
            array (
                'id' => '51e1edd7-d0d2-4aa7-8010-e9857ef1cb60',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'theme',
                'value' => 'dark',
                'label' => 'settings.fields.theme',
                'type' => 'theme_switcher',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-12-01 11:55:59',
            ),
            6 => 
            array (
                'id' => '5e2cadcd-d31f-42f6-a3c1-8efb8189fa09',
                'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'key' => 'default_locale',
                'value' => 'de_DE',
                'label' => 'settings.fields.default_locale',
                'type' => 'string',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            7 => 
            array (
                'id' => '7bc2af66-b583-45d0-b45b-901e0117246f',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'primary_color',
                'value' => '#000000',
                'label' => 'settings.fields.primary_color',
                'type' => 'color',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-12-02 13:27:41',
            ),
            8 => 
            array (
                'id' => '801e04a1-0780-4582-8ae3-a9b53bfdfa60',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'table_striped_rows',
                'value' => '1',
                'label' => 'settings.fields.table_striped_rows',
                'type' => 'bool',
                'autoload' => 0,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-12-01 11:39:24',
            ),
            9 => 
            array (
                'id' => '96f0f636-c928-4eef-ab2f-b5a6fe363b2b',
                'setting_item_id' => 'e6f6cde1-fe51-400a-8710-da218767d42f',
                'key' => 'default_language',
                'value' => 'de',
                'label' => 'settings.fields.default_language',
                'type' => 'string',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            10 => 
            array (
                'id' => 'a24802c0-10cb-41e5-a309-ab32731b8e14',
                'setting_item_id' => 'e6f6cde1-fe51-400a-8710-da218767d42f',
                'key' => 'fallback_language',
                'value' => 'en',
                'label' => 'settings.fields.fallback_language',
                'type' => 'string',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            11 => 
            array (
                'id' => 'b029bed6-3d47-4448-b670-4d2349bd4cef',
                'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'key' => 'date_format',
                'value' => 'd.m.Y',
                'label' => 'settings.fields.date_format',
                'type' => 'string',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            12 => 
            array (
                'id' => 'c3e18c71-0e5b-423b-a84e-e54097259425',
                'setting_item_id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'key' => 'use_individual_module_colors',
                'value' => '1',
                'label' => 'settings.fields.use_individual_module_colors',
                'type' => 'bool',
                'autoload' => 1,
                'created_at' => '2025-11-27 16:34:28',
                'updated_at' => '2025-12-02 13:27:41',
            ),
            13 => 
            array (
                'id' => 'c75b5727-0a2f-4b8d-974a-d72e055f5a00',
                'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'key' => 'timezone',
                'value' => 'Europe/Berlin',
                'label' => 'settings.fields.timezone',
                'type' => 'string',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
            14 => 
            array (
                'id' => 'f2193872-ca1d-4a6e-9bb4-3215bd3fc626',
                'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'key' => 'first_day_of_week',
                'value' => '1',
                'label' => 'settings.fields.first_day_of_week',
                'type' => 'int',
                'autoload' => 1,
                'created_at' => '2025-11-27 12:36:24',
                'updated_at' => '2025-11-27 12:36:24',
            ),
        ));
        
        
    }
}