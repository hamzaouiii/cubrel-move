<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * When a record is assigned to a user they will be notified
 */
class RecordAssignedNotification extends BaseAppNotification
{
    public function __construct(
        protected string $moduleSlug,
        protected string $recordId,
        protected ?string $recordLabel,
        protected ?string $assignedByName,
    ) {}

    public function typeKey(): string
    {
        return 'record_assigned';
    }


    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'module_slug' => $this->moduleSlug,
            'record_id' => $this->recordId,
            'record_label' => $this->recordLabel,
            'actor_name' => $this->assignedByName,
            'url' => "/{$this->moduleSlug}/{$this->recordId}",
            'icon' => 'fa-solid fa-user-tag',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.record_assigned.subject'))
            ->line(__('emails.notifications.record_assigned.body', [
                'record' => $this->recordLabel ?? $this->recordId,
                'user' => $this->assignedByName ?? __('globals.notifications.someone'),
            ]))
            ->action(__('emails.notifications.view_action'), url("/{$this->moduleSlug}/{$this->recordId}"));
    }
}
