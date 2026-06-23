<?php

namespace Database\Seeders;

use App\Models\ListFilter;
use Illuminate\Database\Seeder;

class DefaultFiltersSeeder extends Seeder
{
  public function run(): void
  {
    $defaults = config('default_filters', []);

    foreach ($defaults as $slug => $definition) {
      $moduleSlug = $definition['module_slug'] ?? null;

      ListFilter::updateOrCreate(
        [
          'slug' => $slug,
          'module_slug' => $moduleSlug,
        ],
        [
          'name' => $definition['name'],
          'label' => $definition['label'],
          'user_id' => null,
          'is_shared' => $definition['is_shared'] ?? false,
          'is_system' => $definition['is_system'] ?? true,
          'is_global' => $moduleSlug === null,
          'conditions' => $definition['conditions'],
          'match_type' => $definition['match_type'] ?? 'all',
        ]
      );
    }
  }
}
