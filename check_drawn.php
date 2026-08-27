<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mission = App\Models\Mission::where('title', 'Mega Test Mission')->first();
$drawn = $mission->questionBank->drawQuestions(20);
echo "Drawn: " . count($drawn) . "\n";
