<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Order;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class OrdersModuleHandler extends BaseModuleHandler
{
  protected string $model = Order::class;

  protected function query(array $params = []): Builder
  {
    $query = Order::query();

    // apply filters if needed

    return $query;
  }
}
