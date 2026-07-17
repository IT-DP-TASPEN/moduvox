<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('is_admin', false)->first();
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Office ID: " . ($user->office_id ?? 'NULL') . "\n";
    echo "Office Name: " . ($user->office->name ?? 'N/A') . "\n";
    echo "Profile: " . ($user->profile ? 'Exists' : 'Missing') . "\n";
    if ($user->profile) {
        echo "Education Level: " . ($user->profile->education_level ?? 'NULL') . "\n";
    }
} else {
    echo "No non-admin user found.\n";
}
