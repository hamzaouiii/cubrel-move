<?php

namespace App\Handlers\Modules;

use App\Models\Modules\SupportCase;
use Illuminate\Database\Eloquent\Builder;

class CasesModuleHandler extends BasePaginatedModuleHandler
{
    protected function query(array $params = []): Builder
    {
        $query = SupportCase::query();

        // apply filters here if needed

        return $query;
    }
}
