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

    $relationshipTypes = collect(config('default_relationship_types', []))
      ->map(fn (string $type) => [
        'label' => "relationships.types.{$type}",
        'value' => $type,
      ])
      ->all();

    DropdownList::updateOrCreate(
      ['key' => 'relationship_type_list'],
      [
        'values'    => $relationshipTypes,
        'is_global' => false,
      ]
    );
  }
}
