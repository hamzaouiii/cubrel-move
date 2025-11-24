<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [

            // ============================================================
            // SYSTEM
            // ============================================================
            [
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name'       => 'System Settings',
                'path'       => '/settings/system/general',
                'icon'       => 'fa-solid fa-gear',
                'category'   => 'system',
            ],
            [
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name'       => 'Locale',
                'path'       => '/settings/system/locale',
                'icon'       => 'fa-solid fa-globe',
                'category'   => 'system',
            ],
            [
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name'       => 'Languages',
                'path'       => '/settings/system/languages',
                'icon'       => 'fa-solid fa-language',
                'category'   => 'system',
            ],
            [
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name'       => 'Style',
                'path'       => '/settings/system/style',
                'icon'       => 'fa-solid fa-paint-brush',
                'category'   => 'system',
            ],
            [
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name'       => 'Currencies',
                'path'       => '/settings/system/currencies',
                'icon'       => 'fa-solid fa-coins',
                'category'   => 'system',
            ],


            // ============================================================
            // EMAIL
            // ============================================================
            [
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name'       => 'System Email Settings',
                'path'       => '/settings/email/general',
                'icon'       => 'fa-solid fa-envelope-circle-check',
                'category'   => 'email',
            ],
            [
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name'       => 'Email Queue',
                'path'       => '/settings/email/queue',
                'icon'       => 'fa-solid fa-envelope-open-text',
                'category'   => 'email',
            ],
            [
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name'       => 'Inbound Email',
                'path'       => '/settings/email/inbound',
                'icon'       => 'fa-solid fa-inbox',
                'category'   => 'email',
            ],


            // ============================================================
            // USERS
            // ============================================================
            [
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name'       => 'List Users',
                'path'       => '/settings/users',
                'icon'       => 'fa-solid fa-users',
                'category'   => 'users',
            ],
            [
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name'       => 'Create User',
                'path'       => '/settings/users/create',
                'icon'       => 'fa-solid fa-user-plus',
                'category'   => 'users',
            ],
            [
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name'       => 'Role Management',
                'path'       => '/settings/users/roles',
                'icon'       => 'fa-solid fa-user-shield',
                'category'   => 'users',
            ],


            // ============================================================
            // CUSTOMISATION
            // ============================================================
            [
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name'       => 'Modules',
                'path'       => '/settings/customisation/modules',
                'icon'       => 'fa-solid fa-cubes',
                'category'   => 'customisation',
            ],
            [
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name'       => 'Layouts',
                'path'       => '/settings/customisation/layouts',
                'icon'       => 'fa-solid fa-table-cells-large',
                'category'   => 'customisation',
            ],
            [
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name'       => 'Fields',
                'path'       => '/settings/customisation/fields',
                'icon'       => 'fa-solid fa-list',
                'category'   => 'customisation',
            ],
        ];

        // Append UUID + timestamps
        $items = array_map(function ($item) {
            return array_merge($item, [
                'id'         => Str::uuid()->toString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $items);

        DB::table('setting_items')->insert($items);
    }
}
