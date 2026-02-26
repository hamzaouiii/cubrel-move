<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Opportunity;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class OpportuntiesModuleHandler extends BaseModuleHandler
{
  protected string $model = Opportunity::class;

  protected function query(array $params = []): Builder
  {
    $query = Opportunity::query();

    // apply filters if needed

    return $query;
  }
}
