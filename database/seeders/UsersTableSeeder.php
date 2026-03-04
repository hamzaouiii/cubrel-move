<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{


  public function run()
  {


    \DB::table('users')->delete();

    \DB::table('users')->insert(array(
      0 =>
      array(
        'id' => '2d59ef20-d69a-44cf-8050-b8b0784aa214',
        'username' => 'admin',
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'email_verified_at' => NULL,
        'password' => '$2y$12$005tpCysk.ajpcrMpxSKQOu6rtlfukkUC6d3a2234RTxfp3LwoPDa',
        'is_admin' => 1,
        'remember_token' => NULL,
        'created_at' => '2025-11-20 14:27:21',
        'updated_at' => '2025-11-20 14:27:21',
      ),
      1 =>
      array(
        'id' => '9f616974-e1bf-42d7-aae2-faf40d9b5af5',
        'username' => 'taleb',
        'name' => 'Taleb',
        'email' => 'taleb@example.com',
        'email_verified_at' => NULL,
        'password' => '$2y$12$7wAR.bkkYHy42NSGaE0UPej52zFHe42kYBwT0lLUPtGGFGZ1USw.O',
        'is_admin' => 0,
        'remember_token' => NULL,
        'created_at' => '2025-11-20 14:27:21',
        'updated_at' => '2025-11-20 14:27:21',
      ),
      2 =>
      array(
        'id' => 'f2710fe2-0959-4bf7-be4a-843e01205d51',
        'username' => 'simo',
        'name' => 'Simo',
        'email' => 'simo@example.com',
        'email_verified_at' => NULL,
        'password' => '$2y$12$K6sSMkNzjxFOOyjY7K/1yueJysJWcy.lN7GMCqU8mlwraJCM8ovD.',
        'is_admin' => 1,
        'remember_token' => NULL,
        'created_at' => '2025-11-20 14:27:21',
        'updated_at' => '2025-11-20 14:27:21',
      ),
    ));
  }
}
