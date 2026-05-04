<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Opportunity extends BaseModule
{
  protected $table = 'opportunities';

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
protected $moduleCasts  = [
    'expected_close_date' => 'datetime' ,

];
  protected $guarded = [];
}
