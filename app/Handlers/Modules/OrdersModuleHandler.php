<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Orders;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class OrdersModuleHandler extends BaseModuleHandler
{
    protected string $model = Orders::class;

    protected function query(array $params = []): Builder
    {
        $query = Orders::query();

        // apply filters if needed

        return $query;
    }
}
