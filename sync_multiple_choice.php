<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mission = App\Models\Mission::where('title', 'Mega Test Mission')->first();
$bank = $mission->questionBank;

// Get the multiple_choice ID
$typeId = App\Models\QuizType::where('slug', 'multiple-choice')->first()->id;

// Filter only multiple-choice questions
$legacyQuestions = $bank->questions;
$multipleChoiceIds = $legacyQuestions->where('quiz_type_id', $typeId)->pluck('id')->toArray();

// Sync ONLY multiple-choice questions to assignedQuestions
$bank->assignedQuestions()->sync($multipleChoiceIds);

echo "Synced " . count($multipleChoiceIds) . " multiple-choice questions to the mission.\n";
