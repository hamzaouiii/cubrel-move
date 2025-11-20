<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id'            => Str::uuid(),
                'username'      => 'admin',
                'name'          => 'Admin',
                'email'         => 'admin@example.com',
                'password'      => Hash::make('96qdmoZWXIXYsIRY2*F&#M2Oe'),
                'is_admin'      => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],

            [
                'id'            => Str::uuid(),
                'username'      => 'simo',
                'name'          => 'Simo',
                'email'         => 'simo@example.com',
                'password'      => Hash::make('^ZidV0!hU@1K'),
                'is_admin'      => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],

            [
                'id'            => Str::uuid(),
                'username'      => 'taleb',
                'name'          => 'Taleb',
                'email'         => 'taleb@example.com',
                'password'      => Hash::make('!N6BlCw*A6&o'),
                'is_admin'      => false,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}
