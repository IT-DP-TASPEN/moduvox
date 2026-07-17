<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::find(104);
echo "User: " . $user->name . "\n";
echo "Education Level: " . ($user->profile->education_level ?? 'NULL') . "\n";
echo "Institution: " . ($user->profile->education_institution ?? 'NULL') . "\n";
