<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModule;

class Account extends BaseModule
{

  protected $fillable = [
    'name',
    'website',
    'email',
    'phone',
    'billing_address',
    'shipping_address',
    'city',
    'country',
  ];
}
