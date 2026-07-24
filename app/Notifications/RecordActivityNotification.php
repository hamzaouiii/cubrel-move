<?php

namespace App\Notifications;

use App\Support\NotificationPresenter;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * When a change happens on a record owned by a user they will get this notification
 */
class RecordActivityNotification extends BaseAppNotification
{
    public function __construct(
        protected string $moduleSlug,
        protected string $recordId,
        protected ?string $recordLabel,
        protected ?string $actorName,
        protected string $action,
    ) {}

    public function typeKey(): string
    {
        return 'record_activity';
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'module_slug' => $this->moduleSlug,
            'record_id' => $this->recordId,
            'record_label' => $this->recordLabel,
            'actor_name' => $this->actorName,
            'action' => $this->action,
            'url' => "/{$this->moduleSlug}/{$this->recordId}",
            'icon' => 'fa-solid fa-clock-rotate-left',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $module = NotificationPresenter::moduleLabel($this->moduleSlug) ?? $this->moduleSlug;

        return (new MailMessage)
            ->subject(__('emails.notifications.record_activity.subject', ['module' => $module]))
            ->line(__("emails.notifications.record_activity.body.{$this->action}", [
                'module' => $module,
                'record' => $this->recordLabel ?? $this->recordId,
                'user' => $this->actorName ?? __('globals.notifications.someone'),
            ]))
            ->action(__('emails.notifications.view_action'), url("/{$this->moduleSlug}/{$this->recordId}"));
    }
}
