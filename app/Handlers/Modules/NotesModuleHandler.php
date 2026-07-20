<?php

namespace App\Handlers\Modules;

use App\Models\Modules\Note;
use Illuminate\Database\Eloquent\Builder;

class NotesModuleHandler extends BaseModuleHandler
{
    protected function query(array $params = []): Builder
    {
        return Note::query();
    }

    protected array $searchable = [
        'name',
        'description',
    ];

    protected string $model = Note::class;
}
