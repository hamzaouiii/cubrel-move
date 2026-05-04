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
  protected $guarded = [];
}
