<?php

namespace App\Console\Commands;

use App\Models\UserInvite;
use App\Services\Notifications\NotificationService;
use Illuminate\Console\Command;

class NotifyExpiredInvites extends Command
{
    protected $signature = 'invites:notify-expired';

    protected $description = 'Marks pending invites past their expiry as expired and notifies the inviting admin';

    public function handle(): void
    {
        $invites = UserInvite::query()
            ->whereNull('accepted_at')
            ->whereNull('expired_notified_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($invites as $invite) {
            $invite->timestamps = false;
            $invite->forceFill([
                'status' => 'expired',
                'expired_notified_at' => now(),
            ])->save();

            NotificationService::notifyInviteExpired($invite);
        }

        $this->info("Notified inviters of {$invites->count()} expired invite(s).");
    }
}
