<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('settings')->delete();
        
        \DB::table('settings')->insert(array (
            0 => 
            array (
                'id' => '4a667962-197d-490c-a23b-85e2b01393b6',
                'name' => 'Email',
                'label' => 'settings.groups.email',
                'description' => 'settings.groups.description.email',
                'path' => NULL,
                'category' => NULL,
                'created_at' => '2025-11-23 20:59:12',
                'updated_at' => '2025-11-23 20:59:12',
            ),
            1 => 
            array (
                'id' => '63cdea20-20fe-4278-81e2-086820d637a8',
                'name' => 'System',
                'label' => 'settings.groups.system',
                'description' => 'settings.groups.description.system',
                'path' => NULL,
                'category' => NULL,
                'created_at' => '2025-11-23 20:45:01',
                'updated_at' => '2025-11-23 20:45:01',
            ),
            2 => 
            array (
                'id' => '7fd9c4c9-05d4-465a-9377-2ac5288f209e',
                'name' => 'Users',
                'label' => 'settings.groups.users',
                'description' => 'settings.groups.description.users',
                'path' => NULL,
                'category' => NULL,
                'created_at' => '2025-11-23 20:57:10',
                'updated_at' => '2025-11-23 20:57:10',
            ),
            3 => 
            array (
                'id' => 'f401ed94-f999-42d5-a432-0d3e7c2fb2e2',
                'name' => 'Customisations',
                'label' => 'settings.groups.customisations',
                'description' => 'settings.groups.description.customisations',
                'path' => NULL,
                'category' => NULL,
                'created_at' => '2025-11-23 21:00:48',
                'updated_at' => '2025-11-23 21:00:48',
            ),
        ));
        
        
    }
}