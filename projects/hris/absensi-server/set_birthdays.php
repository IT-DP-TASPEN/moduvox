<?php

use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$month = now()->month;
$users = User::where('is_admin', false)->inRandomOrder()->take(5)->get();

foreach ($users as $u) {
    $day = rand(1, 28);
    $date = '1995-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
    $u->update(['birth_date' => $date]);
    echo "Set birthday for {$u->name} to {$date}\n";
}
