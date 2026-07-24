<?php

namespace App\Models\Modules;

use App\Models\BaseModule;

class Task extends BaseModule
{
    protected $table = 'tasks';

    protected $fillable = [
        'name',
        'description',
        'due_at',
        'status',
        'priority',
        'completed_at',
        'owner_id',
    ];

    protected $moduleCasts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $task) {
            if ($task->isDirty('status') && $task->status === 'completed' && empty($task->completed_at)) {
                $task->completed_at = now();
            }
        });
    }
}
