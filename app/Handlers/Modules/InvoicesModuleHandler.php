<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Invoice;
use Illuminate\Database\Eloquent\Builder;

class InvoicesModuleHandler extends BasePaginatedModuleHandler
{
    protected function query(array $params = []): Builder
    {
        $query = Invoice::query();

        // apply filters here if needed

        return $query;
    }
        protected string $model = Invoice::class;
}
