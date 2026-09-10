<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwalkan pengiriman pengingat setiap jam 07:00 pagi
Schedule::command('monlap:send-reminders')->dailyAt('07:00');

// Generate recurring task cycle di awal hari
Schedule::command('monlap:sync-recurring')->dailyAt('00:01');
