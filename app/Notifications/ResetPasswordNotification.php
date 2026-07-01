<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('emails.reset.subject'))
            ->line(__('emails.reset.intro'))
            ->action(__('emails.reset.action'), $url)
            ->line(__('emails.reset.expires', ['count' => $count]))
            ->line(__('emails.reset.no_action'));
    }
}
