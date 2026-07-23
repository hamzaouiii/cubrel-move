<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * All notification types extend this class
 * shared via() logic
 * database is always the default channel 
 * email is optional and is selectable via user preferences
 */
abstract class BaseAppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    abstract public function typeKey(): string;

    public function via($notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->wantsEmailFor($this->typeKey())) {
            $channels[] = 'mail';
        }

        return $channels;
    }
}
