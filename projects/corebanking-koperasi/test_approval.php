<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$config = \App\Models\ApprovalConfig::where('module_key', 'cifs.create')
    ->where('action', 'CREATE')
    ->where('is_active', true)
    ->first();
echo "Config found: " . ($config ? "YES" : "NO") . "\n";
