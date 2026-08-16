<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\ModuleCategory;
use App\Scopes\AdminOnlyModuleScope;

class ModulesTableSeeder extends Seeder
{
  protected array $categoryIds = [];

  public function run()
  {

    foreach (config('modules') as $module) {
      $categoryKey = $module['category'] ?? 'general';
      unset($module['category']);
      $module['module_category_id'] = $this->resolveCategoryId($categoryKey);

      Module::withoutGlobalScope(AdminOnlyModuleScope::class)
        ->firstOrCreate(['slug' => $module['slug']], $module);
    }
  }

  protected function resolveCategoryId(string $key): string
  {
    if (isset($this->categoryIds[$key])) {
      return $this->categoryIds[$key];
    }

    $langKey = 'modules.categories.' . $key;
    $translated = __($langKey);
    $label = $translated !== $langKey ? $translated : ucfirst(str_replace('_', ' ', $key));

    $category = ModuleCategory::firstOrCreate(
      ['label' => $label],
      ['sort_order' => ModuleCategory::nextSortOrder()]
    );

    return $this->categoryIds[$key] = $category->id;
  }
}
