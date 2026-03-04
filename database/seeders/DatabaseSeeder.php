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
    $this->call(ModulesTableSeeder::class);
    $this->call(ContactMessagesTableSeeder::class);
    $this->call(IconsTableSeeder::class);
    $this->call(UsersTableSeeder::class);
    $this->call(dropdownListSeeder::class);
    $this->call(StockFieldsSeeder::class);
    $this->call(SettingValuesSeeder::class);
    $this->call(RelationshipSeeder::class);
    $this->call(DevSeeder::class);
    $this->call(RelationshipPopulationSeeder::class);
  }
}
