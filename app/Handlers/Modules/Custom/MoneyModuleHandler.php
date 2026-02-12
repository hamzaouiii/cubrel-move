<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Money;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class MoneyModuleHandler extends BaseModuleHandler
{
  protected string $model = Money::class;

  protected function query(array $params = []): Builder
  {
    $query = Money::query();

    // apply filters if needed

    return $query;
  }
}
