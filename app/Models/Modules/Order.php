<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

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
}
