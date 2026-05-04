<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModule;

class Quote extends BaseModule
{
  protected $fillable = [
    'name',
    'number',
    'status',
    'valid_until',
    'currency',
    'subtotal',
    'tax',
    'total',
    'notes',
    'owner_id'
  ];

  protected $modelCasts = [
    'valid_until' => 'date',
    'subtotal'    => 'decimal:2',
    'tax'         => 'decimal:2',
    'total'       => 'decimal:2',
  ];
}
