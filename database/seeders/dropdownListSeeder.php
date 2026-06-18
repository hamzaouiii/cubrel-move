<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DropdownList;
use Illuminate\Support\Str;

class dropdownListSeeder extends Seeder
{
  public function run(): void
  {
    $dropdowns = config('dropdown_lists', []);

    foreach ($dropdowns as $key => $values) {

      DropdownList::updateOrCreate(
        ['key' => $key],
        [
          'values'    => $values,
          'is_global' => false,
        ]
      );
    }
  }
}
