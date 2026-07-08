<?php

namespace App\Observers;

use App\Models\Module;
use App\Models\Modules\LineItem;
use App\Scopes\AdminOnlyModuleScope;


class LineItemTotalsObserver
{
    public function saved(LineItem $lineItem): void
    {
        $this->recalculateParent($lineItem);
    }

    public function deleted(LineItem $lineItem): void
    {
        $this->recalculateParent($lineItem);
    }

    private function recalculateParent(LineItem $lineItem): void
    {
        $parent = $this->resolveParent($lineItem->parent_type, $lineItem->parent_id);

        if (! $parent) {
            return;
        }

        $totals = LineItem::query()
            ->where('parent_type', $lineItem->parent_type)
            ->where('parent_id', $lineItem->parent_id)
            ->selectRaw('
                COALESCE(SUM(subtotal), 0) as subtotal,
                COALESCE(SUM(discount_amount), 0) as discount_amount,
                COALESCE(SUM(tax_amount), 0) as tax_amount,
                COALESCE(SUM(total), 0) as total
            ')
            ->first();

        $parent->forceFill([
            'subtotal' => $totals->subtotal,
            'discount_amount' => $totals->discount_amount,
            'tax_amount' => $totals->tax_amount,
            'total' => $totals->total,
        ])->saveQuietly();
    }


    private function resolveParent(string $parentType, string $parentId): mixed
    {
        $module = Module::withoutGlobalScope(AdminOnlyModuleScope::class)
            ->where('slug', $parentType)
            ->first();

        if (! $module || ! $module->model_class || ! class_exists($module->model_class)) {
            return null;
        }

        return $module->model_class::find($parentId);
    }
}
