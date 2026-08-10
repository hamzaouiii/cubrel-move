<?php

namespace Database\Seeders;

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
    $this->call(IconsTableSeeder::class);
    $this->call(dropdownListSeeder::class);
    $this->call(StockFieldsSeeder::class);
    $this->call(DefaultFiltersSeeder::class);
    $this->call(SettingValuesSeeder::class);
    $this->call(RelationshipDropdownSeeder::class); 
    $this->call(RelationshipSeeder::class);
    $this->call(TransformationSeeder::class);
   $this->call(DashboardPresetSeeder::class);

       $this->call(UsersTableSeeder::class);
      //  $this->call(DevSeeder::class);
      //  $this->call(ActivitySeeder::class);
      //  $this->call(LineItemsSeeder::class);
      //  $this->call(RelationshipPopulationSeeder::class);
      //  $this->call(OwnerAssignmentSeeder::class);
  }
}
