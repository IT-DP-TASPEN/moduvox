<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal Otomatis Demo Seeder setiap akhir bulan jam 23:00 (Otomatis generate absen, cuti, lembur, dan GAJI)
\Illuminate\Support\Facades\Schedule::command('demo:monthly-cycle')->lastDayOfMonth('23:00');
