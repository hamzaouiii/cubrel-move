<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

/**
 * Notifies a user if their account was accessed via impersonation by the super admin
 */
class ImpersonationNotification extends BaseAppNotification
{
    public function __construct(
        protected ?string $impersonatorName,
        protected Carbon $startedAt,
    ) {}

    public function typeKey(): string
    {
        return 'impersonated';
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'actor_name' => $this->impersonatorName,
            'started_at' => $this->startedAt->toIso8601String(),
            'url' => null,
            'icon' => 'fa-solid fa-user-secret',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.impersonated.subject'))
            ->line(__('emails.notifications.impersonated.body', [
                'user' => $this->impersonatorName ?? __('globals.notifications.someone'),
                'time' => $this->startedAt->copy()->locale(app()->getLocale())->translatedFormat('l, d.m.Y H:i'),
            ]));
    }
}
