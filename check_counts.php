<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mission = App\Models\Mission::where('title', 'Mega Test Mission')->first();
$bank = $mission->questionBank;

echo "Assigned: " . $bank->assignedQuestions()->count() . "\n";
echo "Legacy: " . $bank->questions()->count() . "\n";
