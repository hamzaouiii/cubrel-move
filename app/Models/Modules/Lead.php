<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Lead extends BaseModule
{
  protected $fillable = [
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'company',
    'address',
    'description',
    'owner_id',
  ];

  public function getCasts(): array
  {
    return [...parent::getCasts(), 'address' => 'array'];
  }

  public function toSearchResult(): array
  {
    return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->email];
  }

  protected static function booted(): void
  {
    parent::booted();

    static::saving(function ($lead) {
      if ($lead->isDirty('first_name') || $lead->isDirty('last_name')) {
        $lead->name = trim("{$lead->first_name} {$lead->last_name}");
      }
    });
  }
}
