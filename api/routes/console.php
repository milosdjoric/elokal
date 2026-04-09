<?php

use App\Jobs\DetectAbandonedCarts;
use App\Jobs\ExpireLoyaltyPoints;
use App\Jobs\ScheduledExport;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Napuštene korpe — proverava svakih 30 minuta
Schedule::job(new DetectAbandonedCarts)->everyThirtyMinutes();

// Istek loyalty poena — jednom dnevno u ponoć
Schedule::job(new ExpireLoyaltyPoints)->dailyAt('00:00');

// Scheduled export — nedeljni (ponedeljak 06:00) i mesečni (1. u mesecu 06:00)
Schedule::job(new ScheduledExport('all'))->weeklyOn(1, '06:00');
Schedule::job(new ScheduledExport('all'))->monthlyOn(1, '06:00');

// Database backup — dnevni u 03:00, nedeljni nedeljom u 03:30, mesečni 1. u 04:00
Schedule::command('db:backup --type=daily')->dailyAt('03:00');
Schedule::command('db:backup --type=weekly')->weeklyOn(0, '03:30');
Schedule::command('db:backup --type=monthly')->monthlyOn(1, '04:00');
