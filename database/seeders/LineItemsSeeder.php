<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Modules\LineItem;
use App\Models\User;
use App\Scopes\AdminOnlyModuleScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LineItemsSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id');

        $modules = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('has_line_items', true)
            ->get();

        foreach ($modules as $module) {
            if (! $module->model_class || ! class_exists($module->model_class)) {
                continue;
            }

            $recordIds = $module->model_class::query()->pluck('id');

            if ($recordIds->isEmpty()) {
                continue;
            }

            $sourceProducts = $this->sourceProducts($module);

            foreach ($recordIds as $recordId) {
                $itemCount = rand(1, 5);

                for ($sortOrder = 0; $sortOrder < $itemCount; $sortOrder++) {
                    $product = $sourceProducts->isNotEmpty() ? $sourceProducts->random() : null;

                    $item = new LineItem([
                        'parent_type' => $module->slug,
                        'parent_id'   => $recordId,
                        'product_id'  => $product->id ?? null,
                        'owner_id'    => $userIds->isNotEmpty() ? $userIds->random() : null,
                        'name'        => $product->name ?? fake()->words(3, true),
                        'unit'        => $product->unit ?? fake()->randomElement(['pcs', 'hr', 'day', 'box']),
                        'unit_price'  => $product->price ?? fake()->randomFloat(2, 10, 1000),
                        'quantity'    => fake()->numberBetween(1, 10),
                        'discount'    => fake()->boolean(30) ? fake()->numberBetween(1, 20) : 0,
                        'tax_rate'    => $product->tax_rate ?? fake()->numberBetween(0, 25),
                        'sort_order'  => $sortOrder,
                        'note'        => fake()->boolean(15) ? fake()->sentence() : null,
                    ]);

                    $item->id = (string) Str::uuid();
                    $item->calculateTotals()->save();
                }
            }
        }
    }

    protected function sourceProducts(Module $module): \Illuminate\Support\Collection
    {
        $sourceSlug = $module->lineItemSourceModuleSlug();

        if (! $sourceSlug) {
            return collect();
        }

        $sourceModule = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('slug', $sourceSlug)
            ->first();

        if (! $sourceModule || ! $sourceModule->model_class || ! class_exists($sourceModule->model_class)) {
            return collect();
        }

        return $sourceModule->model_class::query()->get();
    }
}
