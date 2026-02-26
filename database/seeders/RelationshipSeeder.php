<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipSeeder extends Seeder
{
  public function run(): void
  {
    $relationships = config('stock_relationships', []);
    $now = now();

    foreach ($relationships as $relationship) {
      DB::table('relationships')->updateOrInsert(
        ['name' => $relationship['name']],
        array_merge($relationship, [
          'id' => uuid_create(UUID_TYPE_RANDOM),
          'created_at' => $now,
          'updated_at' => $now,
        ])
      );
    }
  }
}
