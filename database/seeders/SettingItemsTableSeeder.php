<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingItemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('setting_items')->delete();
        
        \DB::table('setting_items')->insert(array (
            0 => 
            array (
                'id' => '050a7e56-77d4-4fd2-8645-9d35a69b309e',
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name' => 'Role Management',
                'label' => 'settings.items.role_management',
                'path' => '/settings/users/roles',
                'icon' => 'fa-solid fa-user-shield',
                'category' => 'users',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            1 => 
            array (
                'id' => '09b5666e-2e88-4ce7-ab77-9cd7ba6850a0',
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name' => 'Inbound Email',
                'label' => 'settings.items.inbound_email',
                'path' => '/settings/email/inbound',
                'icon' => 'fa-solid fa-inbox',
                'category' => 'email',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            2 => 
            array (
                'id' => '0fedf095-e28f-41cb-ab42-2a5ed9ee9b52',
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'Currencies',
                'label' => 'settings.items.currencies',
                'path' => '/settings/system/currencies',
                'icon' => 'fa-solid fa-coins',
                'category' => 'system',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            3 => 
            array (
                'id' => '1a29ef08-b838-4212-ae65-43d261838660',
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name' => 'Create User',
                'label' => 'settings.items.create_user',
                'path' => '/settings/users/create',
                'icon' => 'fa-solid fa-user-plus',
                'category' => 'users',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            4 => 
            array (
                'id' => '334ddade-7333-4095-93b6-12a57b60f7aa',
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name' => 'Fields',
                'label' => 'settings.items.fields',
                'path' => '/settings/customisation/fields',
                'icon' => 'fa-solid fa-list',
                'category' => 'customisation',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            5 => 
            array (
                'id' => '3561e0a9-376e-4679-bf89-f2e8ef2cfe94',
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name' => 'Email Queue',
                'label' => 'settings.items.email_queue',
                'path' => '/settings/email/queue',
                'icon' => 'fa-solid fa-envelope-open-text',
                'category' => 'email',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            6 => 
            array (
                'id' => '6ebbee6e-8ecc-46c2-ba79-c31413b294b5',
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'Locale',
                'label' => 'settings.items.locale',
                'path' => '/settings/system/locale',
                'icon' => 'fa-solid fa-globe',
                'category' => 'system',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            7 => 
            array (
                'id' => '8e4f2862-2578-4a50-bf48-9a549a9d15a9',
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'Style',
                'label' => 'settings.items.style',
                'path' => '/settings/system/style',
                'icon' => 'fa-solid fa-paint-brush',
                'category' => 'system',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            8 => 
            array (
                'id' => 'a5bac070-7687-4570-8e1c-3fc293d91bde',
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name' => 'Modules',
                'label' => 'settings.items.modules',
                'path' => '/settings/customisation/modules',
                'icon' => 'fa-solid fa-cubes',
                'category' => 'customisation',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            9 => 
            array (
                'id' => 'db0af63c-c56e-411c-8614-c4fcb6554593',
                'setting_id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name' => 'List Users',
                'label' => 'settings.items.list_users',
                'path' => '/settings/users',
                'icon' => 'fa-solid fa-users',
                'category' => 'users',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            10 => 
            array (
                'id' => 'e6e08a9c-61f3-4426-880b-e9258f1fbcf5',
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'System Settings',
                'label' => 'settings.items.system_settings',
                'path' => '/settings/system/general',
                'icon' => 'fa-solid fa-gear',
                'category' => 'system',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            11 => 
            array (
                'id' => 'e6f6cde1-fe51-400a-8710-da218767d42f',
                'setting_id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'Languages',
                'label' => 'settings.items.languages',
                'path' => '/settings/system/languages',
                'icon' => 'fa-solid fa-language',
                'category' => 'system',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            12 => 
            array (
                'id' => 'f89fc338-62c3-445d-b08e-fbe283755f10',
                'setting_id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name' => 'Layouts',
                'label' => 'settings.items.layouts',
                'path' => '/settings/customisation/layouts',
                'icon' => 'fa-solid fa-table-cells-large',
                'category' => 'customisation',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
            13 => 
            array (
                'id' => 'fa8d8375-2937-4a53-aaee-f31f8a371cf1',
                'setting_id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name' => 'System Email Settings',
                'label' => 'settings.items.system_email_settings',
                'path' => '/settings/email/general',
                'icon' => 'fa-solid fa-envelope-circle-check',
                'category' => 'email',
                'created_at' => '2025-11-24 10:15:50',
                'updated_at' => '2025-11-24 10:15:50',
            ),
        ));
        
        
    }
}