<?php

namespace App\Handlers\Modules\Custom;

use App\Models\Modules\Custom\Products;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class ProductsModuleHandler extends BaseModuleHandler
{
    protected string $model = Products::class;

    protected function query(array $params = []): Builder
    {
        $query = Products::query();

        // apply filters if needed

        return $query;
    }
}
