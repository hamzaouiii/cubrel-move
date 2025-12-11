<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Birds;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BasePaginatedModuleHandler;

class BirdsModuleHandler extends BasePaginatedModuleHandler
{
    protected string $model = Birds::class;

    protected function query(array $params = []): Builder
    {
        $query = Birds::query();

        // apply filters if needed

        return $query;
    }
}
