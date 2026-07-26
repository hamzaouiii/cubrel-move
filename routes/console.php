<?php

use App\Support\Settings;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:notify-due-soon')->hourly();
Schedule::command('invites:notify-expired')->hourly();

Schedule::command('model:prune')->daily();
// Wrapped in a closure so Settings::get() (hits the `cache` table) is only
// evaluated when the schedule actually runs, not on every artisan boot —
// routes/console.php loads on every CLI invocation, so an eager call here
// broke `migrate` itself on a fresh database (cache table doesn't exist yet).
Schedule::call(function () {
    Artisan::call('queue:prune-failed', ['--hours' => Settings::get('retention_failed_jobs_days', 30) * 24]);
})->daily();
Schedule::command('auth:clear-resets')->daily();
Schedule::command('images:prune-orphans')->weekly();
Schedule::command('modules:prune-stale-drafts')->daily();
Schedule::command('impersonation:reconcile-stale-sessions')->hourly();
