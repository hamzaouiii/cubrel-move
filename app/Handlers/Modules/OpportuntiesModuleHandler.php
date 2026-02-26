<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Opportunties;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class OpportuntiesModuleHandler extends BaseModuleHandler
{
    protected string $model = Opportunties::class;

    protected function query(array $params = []): Builder
    {
        $query = Opportunties::query();

        // apply filters if needed

        return $query;
    }
}
