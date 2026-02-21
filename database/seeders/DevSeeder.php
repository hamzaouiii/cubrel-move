<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Modules\Invoice;
use App\Models\Modules\Quote;
use App\Models\Modules\SupportCase;

class DevSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Account::factory(20)->create();
    Contact::factory(50)->create();
    Quote::factory(30)->create();
    Invoice::factory(30)->create();
    SupportCase::factory(15)->create();
  }
}
