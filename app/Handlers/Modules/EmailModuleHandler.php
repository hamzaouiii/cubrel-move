<?php

namespace App\Handlers\Modules;

use App\Models\Email;
use Illuminate\Database\Eloquent\Builder;

class EmailModuleHandler extends BasePaginatedModuleHandler
{
    protected function query(array $params = []): Builder
    {
        $query = Email::query();

        // apply filters here if needed

        return $query;
    }
}
