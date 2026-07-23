<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tasks:notify-due-soon')->hourly();
Schedule::command('invites:notify-expired')->hourly();
