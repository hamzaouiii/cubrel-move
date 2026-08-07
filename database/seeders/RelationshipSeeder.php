<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Services\Relationships\RelationshipService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipSeeder extends Seeder
{
  public function run(): void
  {
    $relationships = config('stock_relationships', []);
    $now = now();

    // Insert only a rerun must never touch a relationship that already
    // exists
    foreach ($relationships as $relationship) {
      if (DB::table('relationships')->where('name', $relationship['name'])->exists()) {
        continue;
      }
      DB::table('relationships')->insert(
        array_merge($relationship, [
          'id' => uuid_create(UUID_TYPE_RANDOM),
          'created_at' => $now,
          'updated_at' => $now,
          'is_system' => 1,
        ])
      );
    }

    Module::where('is_activity', true)
      ->orWhere('has_activity', true)
      ->get()
      ->each(fn (Module $module) => RelationshipService::syncActivityRelationships($module));
  }
}
