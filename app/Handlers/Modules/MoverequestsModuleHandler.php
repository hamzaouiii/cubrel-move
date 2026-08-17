<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Moverequests;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class MoverequestsModuleHandler extends BaseModuleHandler
{
    protected string $model = Moverequests::class;

    public function query(array $params = []): Builder
    {
        $query = Moverequests::query();

        // apply filters if needed

        return $query;
    }
}
