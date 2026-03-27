<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Services\Relationships\RelationshipService;

class RelationshipPopulationSeeder extends Seeder
{
  public function run(): void
  {
    $relationships = DB::table('relationships')->get();

    foreach ($relationships as $relationship) {

      // Grab the module slugs as defined in the relationships table
      $leftModuleSlug  = $relationship->left_module;
      $rightModuleSlug = $relationship->right_module;

      // Resolve the Eloquent model classes from the modules table to fetch IDs
      $leftClass  = DB::table('modules')->where('slug', $leftModuleSlug)->value('model_class');
      $rightClass = DB::table('modules')->where('slug', $rightModuleSlug)->value('model_class');

      if (!$leftClass || !$rightClass || !class_exists($leftClass) || !class_exists($rightClass)) {
        continue;
      }

      $leftRecords  = $leftClass::pluck('id');
      $rightRecords = $rightClass::pluck('id');

      if ($leftRecords->isEmpty() || $rightRecords->isEmpty()) {
        continue;
      }

      foreach ($leftRecords as $leftId) {

        $linksToCreate = match ($relationship->type) {
          'one-to-one'   => 1,
          'one-to-many'  => rand(1, 3),
          'many-to-many' => rand(1, 5),
          default        => 1
        };

        for ($i = 0; $i < $linksToCreate; $i++) {

          $rightId = $rightRecords->random();

          try {
            // Updated to pass the module slug instead of the class name
            RelationshipService::link(
              $relationship->name,
              $leftModuleSlug,
              (string) $leftId,
              (string) $rightId
            );
          } catch (\Throwable $e) {
            // ignore duplicates or cardinality conflicts
          }
        }
      }
    }
  }
}
