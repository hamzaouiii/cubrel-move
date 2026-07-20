<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Meeting;
use Illuminate\Database\Eloquent\Builder;

class MeetingsModuleHandler extends BaseModuleHandler
{
    protected function query(array $params = []): Builder
    {
        return Meeting::query();
    }

    protected array $searchable = [
        'name',
        'description',
    ];

    protected string $model = Meeting::class;
}
