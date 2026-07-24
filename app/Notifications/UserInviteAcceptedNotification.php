<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * The Admin who sent a user invite will recieve this invite when it is accepted.
 */
class UserInviteAcceptedNotification extends BaseAppNotification
{
    public function __construct(
        protected string $inviteId,
        protected string $inviteEmail,
        protected ?string $acceptedUserName,
    ) {}

    public function typeKey(): string
    {
        return 'invite_accepted';
    }

    /**
     * Raw data only — no translated text. Title/body are rendered on demand
     * by App\Support\NotificationPresenter, in the viewer's current locale.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'invite_id' => $this->inviteId,
            'invite_email' => $this->inviteEmail,
            'accepted_user_name' => $this->acceptedUserName,
            'url' => '/users/invites',
            'icon' => 'fa-solid fa-user-check',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.invite_accepted.subject'))
            ->line(__('emails.notifications.invite_accepted.body', [
                'user' => $this->acceptedUserName ?? $this->inviteEmail,
            ]))
            ->action(__('emails.notifications.view_action'), url('/users/invites'));
    }
}
