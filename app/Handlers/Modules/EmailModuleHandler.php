<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Email;
use Illuminate\Database\Eloquent\Builder;

class EmailModuleHandler extends BaseModuleHandler
{
  protected function query(array $params = []): Builder
  {
    $query = Email::query();

    // apply filters here if needed

    return $query;
  }
  protected string $model = Email::class;
}
