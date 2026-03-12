<?php

namespace App\Models\Modules;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\BaseModule;

class Contact extends BaseModule
{
  protected $fillable = [
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'position',
    'notes',
  ];
}
