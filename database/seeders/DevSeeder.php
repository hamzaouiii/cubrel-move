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
use App\Models\Modules\Task;
use App\Models\Modules\Call;
use App\Models\Modules\Meeting;
use App\Models\Modules\Note;

class DevSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Account::factory(10)->create();
    Contact::factory(20)->create();
    Lead::factory(100)->create();
    Quote::factory(10)->create();
    Invoice::factory(50)->create();
    SupportCase::factory(12)->create();
    Product::factory()->count(20)->create();
    Deal::factory()->count(100)->create();
    Order::factory()->count(10)->create();
    Task::factory(30)->create();
    Call::factory(40)->create();
    Meeting::factory(20)->create();
    Note::factory(25)->create();
  }
}
