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


    $this->call(SettingsTableSeeder::class);
    $this->call(ModulesTableSeeder::class);
    $this->call(ContactMessagesTableSeeder::class);
    $this->call(IconsTableSeeder::class);
    $this->call(IpWhitelistsTableSeeder::class);
    $this->call(LayoutsTableSeeder::class);
    $this->call(LeadsTableSeeder::class);
    $this->call(SettingItemsTableSeeder::class);
    $this->call(SettingValuesTableSeeder::class);
    $this->call(UsersTableSeeder::class);
    $this->call(GlobalLayoutUpdater::class);
    $this->call(GlobalRecordLayoutUpdater::class);
    $this->call([AccountsTableSeeder::class]);
  }
}
