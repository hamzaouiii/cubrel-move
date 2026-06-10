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
    'total_amount',
    'status',
    'order_date',
    'due_date',
  ];

  public function getCasts(): array
  {
    return [...parent::getCasts(), 'total_amount' => 'decimal:2'];
  }
  public function toSearchResult(): array
  {
    return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->order_number];
  }
  protected $guarded = [];
}
