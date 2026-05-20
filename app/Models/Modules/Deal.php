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
    'currency',
    'sales_stage',
    'probability',
    'expected_close_date',
    'type',
];
   public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label'    => $this->name,
            'sublabel' => $this->probability,
        ]);
    }
protected $moduleCasts  = [
    'expected_close_date' => 'datetime' ,

];
  protected $guarded = [];
}
