<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modules\Account;

class AccountsTableSeeder extends Seeder
{
  public function run(): void
  {
    Account::factory()->count(50)->create();
  }
}
