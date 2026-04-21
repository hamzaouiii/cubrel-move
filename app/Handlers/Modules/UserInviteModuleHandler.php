<?php

namespace App\Handlers\Modules;

use App\Models\UserInvite;
use Illuminate\Database\Eloquent\Builder;

class UserInviteModuleHandler extends BaseModuleHandler
{
  /**
   * The Eloquent model class associated with this module.
   *
   * @var class-string
   */
  protected string $model = UserInvite::class;

  protected function query(array $params = []): Builder
  {
    $query = UserInvite::query();

    // apply filters here if needed

    return $query;
  }
  // Optionally override baseQuery(), filters, etc.
}
