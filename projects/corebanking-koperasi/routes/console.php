<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('deposit:pay-interest')->dailyAt('01:30')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('deposit:process-maturity')->dailyAt('01:35')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('loan:auto-debit')->everyMinute()->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('loan:recalculate-kol')->dailyAt('01:45')->timezone('Asia/Jakarta')->withoutOverlapping();
Schedule::command('accounting:sync-coa-movements')->dailyAt('01:50')->timezone('Asia/Jakarta')->withoutOverlapping();
