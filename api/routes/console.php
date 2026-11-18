<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fetch:bps-data')->daily()->withoutOverlapping();
Schedule::command('bps:fetch-economic-data')->monthlyOn(1, '01:00')->withoutOverlapping();
Schedule::command('bps:fetch-regional-economic-data')->monthlyOn(1, '01:30')->withoutOverlapping();
Schedule::command('fetch:bnpb-data')->daily()->withoutOverlapping();
Schedule::command('fetch:news-insight')->hourly()->withoutOverlapping();
Schedule::command('fetch:exchange-rate')->daily()->withoutOverlapping();
Schedule::command('send:daily-digest')
    ->dailyAt('07:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping();
