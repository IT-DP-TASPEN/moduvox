<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\User;
use App\Models\EmploymentDetail;
use App\Models\PositionAllowanceMaster;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$positions = collect();
// Fetch from users
$positions = $positions->merge(User::whereNotNull('title')->where('title', '!=', '')->pluck('title'));
// Fetch from employment_details
$positions = $positions->merge(EmploymentDetail::whereNotNull('position')->where('position', '!=', '')->pluck('position'));

$uniquePositions = $positions->unique()->filter()->sort();

$count = 0;
foreach ($uniquePositions as $pos) {
    $exists = PositionAllowanceMaster::where('position_name', $pos)->first();
    
    PositionAllowanceMaster::updateOrCreate(
        ['position_name' => $pos],
        [
            'position_allowance' => $exists ? $exists->position_allowance : 500000,
            'max_performance_allowance' => $exists ? $exists->max_performance_allowance : 400000
        ]
    );
    $count++;
}

echo "Berhasil sinkronisasi $count jabatan dari data karyawan ke tabel master.";
