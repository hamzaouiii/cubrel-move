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

    foreach ($relationships as $relationship) {
      DB::table('relationships')->updateOrInsert(
        ['name' => $relationship['name']],
        array_merge($relationship, [
          'id' => uuid_create(UUID_TYPE_RANDOM),
          'created_at' => $now,
          'updated_at' => $now,
          'is_system' => 1,
        ])
      );
    }

    // See RelationshipService::syncActivityRelationships() for the full
    // has_activity x is_activity pairing logic — reused here and in
    // ModuleBuilderController so a module gets wired up the same way whether
    // it's flagged at seed time or toggled later in the Module Builder.
    Module::where('is_activity', true)
      ->orWhere('has_activity', true)
      ->get()
      ->each(fn (Module $module) => RelationshipService::syncActivityRelationships($module));
  }
}
