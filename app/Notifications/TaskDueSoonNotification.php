<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

/**
 * When a task is due in 24h a notification will be sent to the owner
 */
class TaskDueSoonNotification extends BaseAppNotification
{
    public function __construct(
        protected string $taskId,
        protected ?string $taskName,
        protected Carbon $dueAt,
    ) {}

    public function typeKey(): string
    {
        return 'task_due_soon';
    }


    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'task_id' => $this->taskId,
            'task_name' => $this->taskName,
            'due_at' => $this->dueAt->toIso8601String(),
            'url' => "/tasks/{$this->taskId}",
            'icon' => 'fa-solid fa-list-check',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.task_due_soon.subject'))
            ->line(__('emails.notifications.task_due_soon.body', [
                'task' => $this->taskName ?? $this->taskId,
                'due' => $this->dueAt->copy()->locale(app()->getLocale())->diffForHumans(),
            ]))
            ->action(__('emails.notifications.view_action'), url("/tasks/{$this->taskId}"));
    }
}
