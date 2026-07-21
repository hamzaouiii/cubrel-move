<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Task;
use Illuminate\Database\Eloquent\Builder;

class TasksModuleHandler extends BaseModuleHandler
{
    protected function query(array $params = []): Builder
    {
        return Task::query();
    }

    protected array $searchable = [
        'name',
        'description',
    ];

    protected string $model = Task::class;
}
