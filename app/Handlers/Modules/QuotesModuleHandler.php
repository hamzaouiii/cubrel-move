<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Quote;
use Illuminate\Database\Eloquent\Builder;

class QuotesModuleHandler extends BaseModuleHandler
{
  protected function query(array $params = []): Builder
  {
    $query = Quote::query();

    // apply filters here if needed

    return $query;
  }
  protected string $model = Quote::class;
}
