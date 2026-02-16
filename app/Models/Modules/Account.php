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

  public function contacts(): HasMany
  {
    return $this->hasMany(Contact::class);
  }

  public function invoices(): HasMany
  {
    return $this->hasMany(Invoice::class);
  }

  public function quotes(): HasMany
  {
    return $this->hasMany(Quote::class);
  }

  public function cases(): HasMany
  {
    return $this->hasMany(SupportCase::class, 'account_id');
  }
}
