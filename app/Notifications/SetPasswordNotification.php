<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class SetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $count = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(__('emails.set_password.subject'))
            ->line(__('emails.set_password.intro'))
            ->action(__('emails.set_password.action'), $url)
            ->line(__('emails.set_password.expires', ['count' => $count]))
            ->line(__('emails.set_password.no_action'));
    }
}
