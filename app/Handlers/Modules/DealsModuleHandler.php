<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Deal;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class DealsModuleHandler extends BaseModuleHandler
{
  protected string $model = Deal::class;

  protected function query(array $params = []): Builder
  {
    $query = Deal::query();

    // apply filters if needed

    return $query;
  }
}
