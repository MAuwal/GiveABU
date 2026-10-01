<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

if (config('hardening.payment_recovery_enabled')) {
    \Illuminate\Support\Facades\Schedule::command('payments:dispatch-notifications --limit=500')
        ->everyMinute()->onOneServer()->withoutOverlapping(5);
    \Illuminate\Support\Facades\Schedule::command('payments:queue-pending --limit=500')
        ->everyMinute()->onOneServer()->withoutOverlapping(5);
}
