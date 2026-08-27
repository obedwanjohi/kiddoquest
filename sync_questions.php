<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mission = App\Models\Mission::where('title', 'Mega Test Mission')->first();
$bank = $mission->questionBank;

// Get ALL legacy questions
$legacyIds = $bank->questions()->pluck('id')->toArray();

// Sync them to assignedQuestions so BOTH match
$bank->assignedQuestions()->sync($legacyIds);

echo "Synced " . count($legacyIds) . " questions to assignedQuestions.\n";
