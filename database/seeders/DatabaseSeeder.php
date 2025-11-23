<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
      // $this->call(ModulesSeeder::class);
      // $this->call(LayoutsSeeder::class);
      // $this->call(UsersSeeder::class);
      $this->call(SettingsLayoutSeeder::class);
      $this->call(SettingsModuleSeeder::class);
    }
}
