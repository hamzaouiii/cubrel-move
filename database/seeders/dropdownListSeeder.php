<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DropDownList;
use Illuminate\Support\Str;

class dropdownListSeeder extends Seeder
{
  public function run(): void
  {
    $dropdowns = config('dropdown_lists', []);

    foreach ($dropdowns as $key => $values) {

      DropDownList::updateOrCreate(
        ['key' => $key],
        [
          'values'    => $values,
          'is_global' => false,
        ]
      );
    }
  }
}
