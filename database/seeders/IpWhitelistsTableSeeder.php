<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class IpWhitelistsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ip_whitelists')->delete();
        
        \DB::table('ip_whitelists')->insert(array (
            0 => 
            array (
                'id' => '1',
                'ip' => '127.0.0.1',
                'active' => 1,
                'label' => 'localhost',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}