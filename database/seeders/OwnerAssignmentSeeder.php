<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OwnerAssignmentSeeder extends Seeder
{
  protected $modules = [
    'accounts',
    'contacts', 
    'invoices',
    'leads',
    'quotes',
    'cases',
    'products',
    'deals',
    'orders',
  ];

  public function run(): void
  {
    $users = User::all();
    
    if ($users->isEmpty()) {
      $this->command->error('No users found. Please seed users first.');
      return;
    }

    foreach ($this->modules as $table) {
      if (!Schema::hasTable($table)) {
        continue;
      }

      $records = DB::table($table)
        ->whereNull('owner_id')
        ->orWhere('owner_id', '')
        ->get();

      foreach ($records as $record) {
        DB::table($table)
          ->where('id', $record->id)
          ->update([
            'owner_id' => $users->random()->id
          ]);
      }

    }
  }
}