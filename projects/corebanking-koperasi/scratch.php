<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
foreach ($tables as $t) {
    $count = \Illuminate\Support\Facades\DB::table($t->name)->count();
    if ($count == 0) {
        echo "EMPTY: " . $t->name . PHP_EOL;
    }
}
