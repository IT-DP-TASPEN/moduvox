<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('banpot:validate')
    ->everyFiveMinutes()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Validation process completed successfully.'))
    ->onFailure(fn() => Log::error('Validation process failed.'));

Schedule::command('saving:process')
    ->everySecond()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Validation Saving completed successfully.'))
    ->onFailure(fn() => Log::error('Validation Saving process failed.'));

Schedule::command('saving:process-internal')
    ->everySecond()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Validation Saving completed successfully.'))
    ->onFailure(fn() => Log::error('Validation Saving process failed.'));

Schedule::command('app:process-create-cif')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Create CIF completed successfully.'))
    ->onFailure(fn() => Log::error('Create CIF process failed.'));

Schedule::command('app:process-create-cif-internal')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Create CIF completed successfully.'))
    ->onFailure(fn() => Log::error('Create CIF process failed.'));


Schedule::command('app:process-create-saving')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Create Saving completed successfully.'))
    ->onFailure(fn() => Log::error('Create Saving process failed.'));

Schedule::command('app:process-create-saving-internal')
    ->everyMinute()
    ->runInBackground()
    ->withoutOverlapping()
    ->onSuccess(fn() => Log::info('Create Saving completed successfully.'))
    ->onFailure(fn() => Log::error('Create Saving process failed.'));
