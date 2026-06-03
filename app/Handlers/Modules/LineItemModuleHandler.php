<?php

namespace App\Handlers\Modules;

use App\Models\Modules\LineItem;
use Illuminate\Database\Eloquent\Builder;

class LineItemModuleHandler extends BaseModuleHandler
{
  protected function query(array $params = []): Builder
  {
    $query = LineItem::query();

    // apply filters here if needed

    return $query;
  }
  protected string $model = LineItem::class;
}
