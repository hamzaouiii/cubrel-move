<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
  public function run(): void
  {

    $now = '2025-11-20 14:27:21';
    $password = '$2y$12$A7DhgQP3cQOO7wQBsjHs3OjrE6COTFFegIYdMk3Gzxle7DiPluwI2';

    $users = [
      // ── Admins ──────────────────────────────────────────────────
      [
        'id'                       => '2d59ef20-d69a-44cf-8050-b8b0784aa214',
        'username'                 => 'admin',
        'name'                      => 'System Admin',
        'type'                     => 'admin',
        'first_name'               => 'System',
        'last_name'                => 'Admin',
        'description'              => null,
        'email'                    => 'admin@example.com',
        'email_verified_at'        => $now,
        'password'                 => $password,
        'two_factor_secret'        => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at'  => null,
        'last_login_at'            => null,
        'last_login_ip'            => null,
        'status'                   => 'active',
        'is_admin'                 => 1,
        'password_changed_at'      => null,
        'failed_login_attempts'    => 0,
        'locked_until'             => null,
        'title'                    => 'mr',
        'phone'                    => null,
        'mobile'                   => null,
        'avatar'                   => null,
        'locale'                   => 'en',
        'timezone'                 => 'UTC',
        'date_format'              => 'Y-m-d',
        'time_format'              => 'H:i',
        'theme'                    => 'system',
        'remember_token'           => null,
        'deleted_at'               => null,
        'created_at'               => $now,
        'updated_at'               => $now,
      ],
      [
        'id'                       => 'f2710fe2-0959-4bf7-be4a-843e01205d51',
        'username'                 => 'simo',
        'name'                      => 'Simo Founder',
        'type'                     => 'executive',
        'first_name'               => 'Simo',
        'last_name'                => 'Founder',
        'description'              => 'Platform owner and lead developer.',
        'email'                    => 'simo@example.com',
        'email_verified_at'        => $now,
        'password'                 => $password,
        'two_factor_secret'        => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at'  => null,
        'last_login_at'            => null,
        'last_login_ip'            => null,
        'status'                   => 'active',
        'is_admin'                 => 1,
        'password_changed_at'      => null,
        'failed_login_attempts'    => 0,
        'locked_until'             => null,
        'title'                    => null,
        'phone'                    => '+49 69 123456',
        'mobile'                   => '+49 151 9876543',
        'avatar'                   => null,
        'locale'                   => 'en',
        'timezone'                 => 'Europe/Berlin',
        'date_format'              => 'd.m.Y',
        'time_format'              => 'H:i',
        'theme'                    => 'dark',
        'remember_token'           => null,
        'deleted_at'               => null,
        'created_at'               => $now,
        'updated_at'               => $now,
      ],

      // ── Regular users ────────────────────────────────────────────
      [
        'id'                       => '9f616974-e1bf-42d7-aae2-faf40d9b5af5',
        'username'                 => 'taleb',
        'name'                 => 'Taleb Mansouri',
        'type'                    => 'sales_rep',
        'first_name'               => 'Taleb',
        'last_name'                => 'Mansouri',
        'description'              => null,
        'email'                    => 'taleb@example.com',
        'email_verified_at'        => $now,
        'password'                 => $password,
        'two_factor_secret'        => null,
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at'  => null,
        'last_login_at'            => null,
        'last_login_ip'            => null,
        'status'                   => 'active',
        'is_admin'                 => 0,
        'password_changed_at'      => null,
        'failed_login_attempts'    => 0,
        'locked_until'             => null,
        'title'                    => 'mr',
        'phone'                    => null,
        'mobile'                   => '+49 176 1112233',
        'avatar'                   => null,
        'locale'                   => 'en',
        'timezone'                 => 'Europe/Berlin',
        'date_format'              => 'd.m.Y',
        'time_format'              => 'H:i',
        'theme'                    => 'system',
        'remember_token'           => null,
        'deleted_at'               => null,
        'created_at'               => $now,
        'updated_at'               => $now,
      ],
    ];

    DB::table('users')->insert($users);
    User::factory(80)->create();

  }
}
