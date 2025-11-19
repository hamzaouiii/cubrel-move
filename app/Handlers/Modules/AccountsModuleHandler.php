<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Account;
use Illuminate\Database\Eloquent\Builder;

class AccountsModuleHandler extends BasePaginatedModuleHandler
{
    protected function query(array $params = []): Builder
    {
        $query = Account::query();

        // apply filters here if needed

        return $query;
    }
}
