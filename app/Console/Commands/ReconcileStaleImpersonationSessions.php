<?php

namespace App\Console\Commands;

use App\Models\ImpersonationSession;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReconcileStaleImpersonationSessions extends Command
{
    protected $signature = 'impersonation:reconcile-stale-sessions';

    protected $description = 'Closes out impersonation sessions whose underlying Laravel session has expired or vanished without an explicit "leave impersonation" click';

    public function handle(): void
    {
        $lifetimeCutoff = now()->subMinutes((int) config('session.lifetime'));

        $liveSessions = DB::table('sessions')->pluck('last_activity', 'id');

        $ongoing = ImpersonationSession::whereNull('ended_at')->get();

        $reconciled = 0;

        foreach ($ongoing as $impersonationSession) {
            $lastActivity = $impersonationSession->laravel_session_id
                ? $liveSessions->get($impersonationSession->laravel_session_id)
                : null;

            if ($lastActivity === null) {
                // Either there's no session ID to check at all or the underlying
                // session row is gone entirely most likely it ran its
                // full course and was later garbage-collected, not that it
                // ended the instant it started. Estimate it ran the full
                // configured lifetime, capped at now().
                $estimatedEnd = $impersonationSession->started_at->copy()
                    ->addMinutes((int) config('session.lifetime'))
                    ->min(now());

                $impersonationSession->update(['ended_at' => $estimatedEnd]);
                $reconciled++;

                continue;
            }

            $lastActivityAt = Carbon::createFromTimestamp($lastActivity);

            if ($lastActivityAt->lt($lifetimeCutoff)) {
                $impersonationSession->update(['ended_at' => $lastActivityAt]);
                $reconciled++;
            }
        }

        $this->info("Reconciled {$reconciled} stale impersonation session(s).");
    }
}
