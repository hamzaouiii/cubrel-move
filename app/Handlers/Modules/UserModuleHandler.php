<?php

namespace App\Handlers\Modules;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserModuleHandler extends BaseModuleHandler
{
  /**
   * The Eloquent model class associated with this module.
   *
   * @var class-string
   */
  protected string $model = User::class;

  protected function query(array $params = []): Builder
  {
    $query = User::query();

    // apply filters here if needed

    return $query;
  }
  // Optionally override baseQuery(), filters, etc.
}
