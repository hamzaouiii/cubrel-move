<?php

namespace App\Models;

use App\Support\Settings;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    use Prunable;

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(Settings::get('retention_notifications_days', 180)));
    }
}
