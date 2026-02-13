<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModule;

class Contact extends BaseModule
{
  protected $fillable = [
    'name',
    'account_id',
    'first_name',
    'last_name',
    'email',
    'phone',
    'position',
    'notes',
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class);
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
    return $this->hasMany(SupportCase::class, 'contact_id');
  }
}
