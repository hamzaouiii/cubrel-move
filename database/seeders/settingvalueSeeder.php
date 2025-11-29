<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Settings\SettingValue;

class settingvalueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SettingValue::updateOrCreate(
        ['key' => 'app_locale',
        'setting_item_id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5'
        ],
        [
            'value'    => 'en', 
            'type'     => 'string',
            'autoload' => true,
        ]
    );
    }
}
