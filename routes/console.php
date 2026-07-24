<?php

use App\Support\Settings;
use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:notify-due-soon')->hourly();
Schedule::command('invites:notify-expired')->hourly();

Schedule::command('model:prune')->daily();
Schedule::command('queue:prune-failed', ['--hours' => Settings::get('retention_failed_jobs_days', 30) * 24])->daily();
Schedule::command('auth:clear-resets')->daily();
