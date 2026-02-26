<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Product;
use Illuminate\Database\Eloquent\Builder;
use App\Handlers\Modules\BaseModuleHandler;

class ProductsModuleHandler extends BaseModuleHandler
{
  protected string $model = Product::class;

  protected function query(array $params = []): Builder
  {
    $query = Product::query();

    // apply filters if needed

    return $query;
  }
}
