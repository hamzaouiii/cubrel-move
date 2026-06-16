<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Account extends BaseModule
{

  protected $fillable = [
    'name',
    'website',
    'email',
    'phone',
    'billing_address',
    'description',
    'shipping_address',
    'owner_id',
  ];

  public function getCasts(): array
  {
    return array_merge(parent::getCasts(), [
      'billing_address'  => 'array',
      'shipping_address' => 'array',
    ]);
  }

      public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->website,
        ]);
    }
}
