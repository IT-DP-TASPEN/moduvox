<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Artisan::call('db:seed', ['--class' => 'ProductSeeder']);
    echo "SUCCESS: " . Artisan::output();
} catch (\Exception $e) {
    file_put_contents(__DIR__.'/seeder_error.log', 
        "ERROR: " . $e->getMessage() . "\n" .
        "FILE: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n" .
        ($e instanceof \Illuminate\Database\QueryException ? "SQL: " . $e->getSql() . "\n" : "") .
        "TRACE: " . $e->getTraceAsString()
    );
    echo "ERROR written to scratch/seeder_error.log\n";
}
