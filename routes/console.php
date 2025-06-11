<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:cleanup-expired-cache')->everyMinute();
Schedule::command('tickets:mark-missed')->twiceDaily();
Schedule::command('app:notify-customers-to-new-events')->everyThirtyMinutes();
Schedule::command('app:notify-customers-for-tomorrow-event')->everyMinute();
