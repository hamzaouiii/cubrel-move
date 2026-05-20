<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Product extends BaseModule
{
  protected $table = 'products';
protected $fillable = [
    'name',
    'owner_id',
    'sku',
    'category',
    'price',
    'currency',
    'is_active',
];

     public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->sku,
        ]);
    }
  protected $guarded = [];
}
