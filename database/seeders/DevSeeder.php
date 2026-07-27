<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Modules\Invoice;
use App\Models\Modules\Lead;
use App\Models\Modules\Quote;
use App\Models\Modules\SupportCase;
use App\Models\Modules\Deal;
use App\Models\Modules\Product;
use App\Models\Modules\Order;

class DevSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Account::factory(10)->create();
    Contact::factory(20)->create();
    Lead::factory(12)->create();
    Quote::factory(10)->create();
    Invoice::factory(10)->create();
    SupportCase::factory(12)->create();
    Product::factory()->count(20)->create();
    Deal::factory()->count(15)->create();
    Order::factory()->count(10)->create();
    // Tasks/Calls/Meetings/Notes are seeded (and linked to the records above)
    // by ActivitySeeder, once relationships exist to link them through.
  }
}
