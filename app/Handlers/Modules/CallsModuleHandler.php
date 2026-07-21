<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Call;
use Illuminate\Database\Eloquent\Builder;

class CallsModuleHandler extends BaseModuleHandler
{
    protected function query(array $params = []): Builder
    {
        return Call::query();
    }

    protected array $searchable = [
        'name',
        'description',
    ];

    protected string $model = Call::class;
}
