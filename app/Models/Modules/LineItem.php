<?php

namespace App\Models\Modules;

use App\Models\BaseModule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LineItem extends BaseModule
{
    protected $fillable = [
        'parent_type',
        'parent_id',
        'product_id',
        'name',
        'unit',
        'unit_price',
        'quantity',
        'discount',
        'tax_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'sort_order',
        'note',
    ];

    protected $casts = [
        'unit_price' => 'decimal:4',
        'quantity' => 'decimal:4',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'subtotal' => 'decimal:4',
        'discount_amount' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total' => 'decimal:4',
        'sort_order' => 'integer',
    ];


    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Modules\Product::class);
    }

    public function calculateTotals(): static
    {
        $unitPrice = (float) ($this->unit_price ?? 0);
        $quantity = (float) ($this->quantity ?? 0);
        $discount = (float) ($this->discount ?? 0);
        $taxRate = (float) ($this->tax_rate ?? 0);

        $subtotal = $unitPrice * $quantity;
        $discountAmount = $subtotal * ($discount / 100);
        $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);

        $this->subtotal = $subtotal;
        $this->discount_amount = $discountAmount;
        $this->tax_amount = $taxAmount;
        $this->total = $subtotal - $discountAmount + $taxAmount;

        return $this;
    }
}
