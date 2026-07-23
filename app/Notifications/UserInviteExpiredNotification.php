<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * The Admin who sent a user invite will recieve this invite when it is expired.
 */
class UserInviteExpiredNotification extends BaseAppNotification
{
    public function __construct(
        protected string $inviteId,
        protected string $inviteEmail,
    ) {}

    public function typeKey(): string
    {
        return 'invite_expired';
    }


    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'invite_id' => $this->inviteId,
            'invite_email' => $this->inviteEmail,
            'url' => '/users/invites',
            'icon' => 'fa-solid fa-user-clock',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.invite_expired.subject'))
            ->line(__('emails.notifications.invite_expired.body', ['email' => $this->inviteEmail]))
            ->action(__('emails.notifications.view_action'), url('/users/invites'));
    }
}
