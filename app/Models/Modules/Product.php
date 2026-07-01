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
    'is_active',
    'tax_rate',
  ];

  public function getCasts(): array
  {
    return [...parent::getCasts(), 'price' => 'decimal:2', 'is_active' => 'boolean'];
  }

  public function toSearchResult(): array
  {
    return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->sku];
  }
  protected $guarded = [];
}
