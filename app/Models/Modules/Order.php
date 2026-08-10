<?php

namespace App\Models\Modules;

use App\Models\BaseModule;
use Illuminate\Validation\ValidationException;

class Order extends BaseModule
{
  protected $table = 'orders';
  protected $fillable = [
    'name',
    'owner_id',
    'order_number',
    'status',
    'order_date',
    'due_date',
    'subtotal',
    'discount_amount',
    'tax_amount',
    'total',
  ];

  protected $moduleCasts = [
    'order_date' => 'date',
    'due_date'   => 'date',
  ];

  public function toSearchResult(): array
  {
    return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->order_number];
  }
  protected $guarded = [];

  protected static function booted(): void
  {
    parent::booted();

    static::saving(function (self $order) {
      if ($order->order_date && $order->due_date && $order->order_date->gt($order->due_date)) {
        throw ValidationException::withMessages([
          'due_date' => 'Due date must be after order date.',
        ]);
      }
    });
  }
}
