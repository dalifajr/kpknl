<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SyncBmnDataCommand;

// Jadwalkan auto-update data dari spreadsheet setiap 1 jam sekali
Schedule::command('bmn:sync --source=AUTO_SCHEDULER')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
