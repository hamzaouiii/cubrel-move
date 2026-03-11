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

      $leftClass  = $relationship->left_class;
      $rightClass = $relationship->right_class;

      if (!class_exists($leftClass) || !class_exists($rightClass)) {
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

            RelationshipService::link(
              $relationship->name,
              $leftClass,
              $leftId,
              $rightId
            );
          } catch (\Throwable $e) {
            // ignore duplicates or cardinality conflicts
          }
        }
      }
    }
  }
}
