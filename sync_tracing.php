<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mission;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;

$mission = Mission::where('title', 'Mega Test Mission')->first();
$bank = $mission->questionBank;

$type = QuizType::where('slug', 'tracing')->first();
if (!$type) {
    $type = QuizType::firstOrCreate(['slug' => 'tracing'], ['name' => 'Tracing', 'code' => 'QT-12']);
}

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];

// 1. Tracing Question
$q1 = QuizQuestion::create([
    'question_bank_id' => $bank->id, 
    'quiz_type_id' => $type->id, 
    'prompt' => "Trace the letter B", 
    'metadata' => ['character' => 'B'],
    'points' => 10, 
    'order' => 1
]);

$syncIds[] = $q1->id;

$bank->assignedQuestions()->sync($syncIds);

echo "Created 1 tracing question.\n";
