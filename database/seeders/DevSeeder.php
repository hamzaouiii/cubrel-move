<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modules\Account;
use App\Models\Modules\Contact;
use App\Models\Modules\Invoice;
use App\Models\Modules\Lead;
use App\Models\Modules\Quote;
use App\Models\Modules\SupportCase;
use App\Models\Modules\Opportunity;
use App\Models\Modules\Product;
use App\Models\Modules\Order;

class DevSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Account::factory(80)->create();
    Contact::factory(80)->create();
    Lead::factory(100)->create();
    Quote::factory(30)->create();
    Invoice::factory(100)->create();
    SupportCase::factory(25)->create();
    Product::factory()->count(50)->create();
    Opportunity::factory()->count(50)->create();
    Order::factory()->count(50)->create();
  }
}
