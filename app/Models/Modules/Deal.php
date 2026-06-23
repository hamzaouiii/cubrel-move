<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Deal extends BaseModule
{
  protected $table = 'deals';

  protected $fillable = [
    'name',
    'owner_id',
    'amount',
    'sales_stage',
    'probability',
    'expected_close_date',
    'type',
  ];

  public function getCasts(): array
  {
    return [...parent::getCasts(), 'amount' => 'decimal:2', 'expected_close_date' => 'date'];
  }

  public function toSearchResult(): array
  {
    return [...parent::toSearchResult(), 'label' => $this->name, 'sublabel' => $this->probability];
  }
  protected $guarded = [];
}
