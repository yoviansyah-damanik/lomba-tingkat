<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Pengingat push H-30 menit sebelum sesi lomba dimulai
Schedule::command('schedules:notify-upcoming')->everyMinute()->withoutOverlapping();
