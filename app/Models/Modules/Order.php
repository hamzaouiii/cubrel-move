<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Order extends BaseModule
{
  protected $table = 'orders';

  protected $guarded = [];
}
