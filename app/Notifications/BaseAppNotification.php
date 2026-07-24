<?php

namespace App\Notifications;

use App\Support\NotificationPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\App;

/**
 * All notification types extend this class
 * shared via() logic
 * Database (Bell) and Braodcast (Live toast) are in-app 
 * mail is email
 * all notifications are selectable and toggable both system wide and per user 
 */
abstract class BaseAppNotification extends Notification implements ShouldQueue, ShouldBroadcast
{
    use Queueable;

    abstract public function typeKey(): string;

    public function via($notifiable): array
    {
        $channels = [];

        if ($notifiable->wantsInAppFor($this->typeKey())) {
            $channels[] = 'database';
            $channels[] = 'broadcast';
        }

        if ($notifiable->wantsEmailFor($this->typeKey())) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    // broadcast has no "read time" request to render title/body in the
    // viewer's locale so render here explicitly in the recipient's own preferred locale
    public function toBroadcast($notifiable): BroadcastMessage
    {
        $data = $this->toArray($notifiable);

        $previousLocale = App::getLocale();
        App::setLocale($notifiable->preferredLocale());
        $rendered = NotificationPresenter::present($data['type'], $data);
        App::setLocale($previousLocale);

        return new BroadcastMessage(array_merge($data, $rendered));
    }
}
