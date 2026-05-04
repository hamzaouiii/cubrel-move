<?php

namespace App\Models\Modules;

use App\Models\BaseModule;
use App\Concerns\HasFullName;

class Contact extends BaseModule
{
  use HasFullName;
  protected $fillable = [
    'name',
    'first_name',
    'last_name',
    'email',
    'phone',
    'position',
    'notes',
    'owner_id'
  ];
}
