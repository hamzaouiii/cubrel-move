<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Moves;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class MovesModuleHandler extends BaseModuleHandler
{
    protected string $model = Moves::class;

    public function query(array $params = []): Builder
    {
        $query = Moves::query();

        // apply filters if needed

        return $query;
    }
}
