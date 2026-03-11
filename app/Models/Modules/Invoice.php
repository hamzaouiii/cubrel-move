<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BaseModule;

class Invoice extends BaseModule
{
  protected $fillable = [
    'name',
    'number',
    'status',
    'issue_date',
    'due_date',
    'currency',
    'subtotal',
    'tax',
    'total',
    'notes',
  ];

  protected $casts = [
    'issue_date' => 'date',
    'due_date'   => 'date',
    'subtotal'   => 'decimal:2',
    'tax'        => 'decimal:2',
    'total'      => 'decimal:2',
  ];
}
