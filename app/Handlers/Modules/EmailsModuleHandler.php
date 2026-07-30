<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Email;
use Illuminate\Database\Eloquent\Builder;

class EmailsModuleHandler extends BaseModuleHandler
{
    protected function query(array $params = []): Builder
    {
        return Email::query();
    }

    protected array $searchable = [
        'name',
        'body',
        'from_address',
        'from_name',
    ];

    protected string $model = Email::class;
}
