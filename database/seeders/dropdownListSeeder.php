<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DropDownList;
use Illuminate\Support\Str;

class dropdownListSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'cases_status_list',
        'is_global' => false,
        'field_key' => 'cases_status',
        'values' => [
          ['value' => 'open', 'label' => 'dropdowns.cases_status_list.open'],
          ['value' => 'in_progress', 'label' => 'dropdowns.cases_status_list.in_progress'],
          ['value' => 'pending_input', 'label' => 'dropdowns.cases_status_list.pending_input'],
          ['value' => 'rejected', 'label' => 'dropdowns.cases_status_list.rejected'],
          ['value' => 'closed', 'label' => 'dropdowns.cases_status_list.closed'],
        ],
      ]
    );
    DropdownList::create(
      [
        'id' => Str::uuid(),
        'key' => 'cases_priority_list',
        'is_global' => false,
        'field_key' => 'cases_priority',
        'values' => [
          ['value' => 'low', 'label' => 'dropdowns.cases_priority_list.low'],
          ['value' => 'medium', 'label' => 'dropdowns.cases_priority_list.medium'],
          ['value' => 'hight', 'label' => 'dropdowns.cases_priority_list.high'],
          ['value' => 'urgent', 'label' => 'dropdowns.cases_priority_list.urgent'],
        ],
      ]
    );
  }
}
