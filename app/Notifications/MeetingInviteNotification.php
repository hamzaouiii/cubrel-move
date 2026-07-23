<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
/**
 * Notifies A user if they invited to a meeting
 */
class MeetingInviteNotification extends BaseAppNotification
{
    public function __construct(
        protected string $meetingId,
        protected ?string $meetingName,
        protected ?string $invitedByName,
        protected ?string $role,
    ) {}

    public function typeKey(): string
    {
        return 'meeting_invite';
    }


    public function toArray($notifiable): array
    {
        return [
            'type' => $this->typeKey(),
            'meeting_id' => $this->meetingId,
            'meeting_name' => $this->meetingName,
            'actor_name' => $this->invitedByName,
            'role' => $this->role,
            'url' => "/meetings/{$this->meetingId}",
            'icon' => 'fa-solid fa-calendar-check',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('emails.notifications.meeting_invite.subject'))
            ->line(__('emails.notifications.meeting_invite.body', [
                'meeting' => $this->meetingName ?? __('globals.notifications.a_meeting'),
                'user' => $this->invitedByName ?? __('globals.notifications.someone'),
            ]))
            ->action(__('emails.notifications.view_action'), url("/meetings/{$this->meetingId}"));
    }
}
