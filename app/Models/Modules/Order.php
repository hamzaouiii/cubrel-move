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
    'currency',
    'status',
    'order_date',
    'due_date',
];
     public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->number,
        ]);
    }
  protected $guarded = [];
}
