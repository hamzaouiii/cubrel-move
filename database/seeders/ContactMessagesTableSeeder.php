<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ContactMessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('contact_messages')->delete();
        
        \DB::table('contact_messages')->insert(array (
            0 => 
            array (
                'id' => '019aad7a-e3b9-71f3-822f-ce472726831a',
                'name' => 'Simo Hamzaoui Number six',
                'email' => 'simo@hamzaoui.cc',
                'email_confirmation' => 0,
                'phone' => NULL,
                'message' => 'This is a test',
                'status' => 'new',
                'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0',
                'created_at' => '2025-11-22 21:31:43',
                'updated_at' => '2025-11-22 22:04:05',
            ),
            1 => 
            array (
                'id' => '019aad9a-3494-70cd-a82c-c09c2fd24c90',
                'name' => 'Samuel L Jackson',
                'email' => 'Samuel.l.jackson@gmailxxxys.com',
                'email_confirmation' => 0,
                'phone' => NULL,
                'message' => 'Thank you for hosting me the other day.',
                'status' => 'new',
                'ip' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0',
                'created_at' => '2025-11-22 22:05:55',
                'updated_at' => '2025-11-22 22:05:55',
            ),
            2 => 
            array (
                'id' => '019ae4cf-f3b2-7108-bbbd-33bdc551bf08',
                'name' => 'test',
                'email' => NULL,
                'email_confirmation' => 0,
                'phone' => NULL,
                'message' => NULL,
                'status' => 'new',
                'ip' => NULL,
                'user_agent' => NULL,
                'created_at' => '2025-12-03 15:23:44',
                'updated_at' => '2025-12-03 15:23:44',
            ),
        ));
        
        
    }
}