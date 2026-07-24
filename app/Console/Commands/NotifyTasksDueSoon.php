<?php

namespace App\Console\Commands;

use App\Models\Modules\Task;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;

class NotifyTasksDueSoon extends Command
{
    protected $signature = 'tasks:notify-due-soon';

    protected $description = 'Notifies task owners about tasks due within the next 24 hours';

    public function handle(): void
    {
        $tasks = Task::query()
            ->whereNull('due_soon_notified_at')
            ->whereNotNull('owner_id')
            ->where('status', '!=', 'completed')
            ->whereBetween('due_at', [now(), now()->addHours(24)])
            ->get();

        foreach ($tasks as $task) {
            NotificationService::notifyTaskDueSoon($task);

            $task->forceFill(['due_soon_notified_at' => now()])->save();
        }

        $this->info("Notified owners of {$tasks->count()} task(s) due soon.");
    }
}
