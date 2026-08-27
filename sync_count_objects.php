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
$type = QuizType::where('slug', 'count-objects')->first();
if (!$type) {
    $type = QuizType::where('slug', 'count_objects')->first();
}
if (!$type) {
    $type = QuizType::firstOrCreate(['slug' => 'count-objects'], ['name' => 'Count Objects', 'code' => 'QT-98']);
}

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];

// 1. Count objects question
$q1 = QuizQuestion::create([
    'question_bank_id' => $bank->id, 
    'quiz_type_id' => $type->id, 
    'prompt' => "How many apples are there?", 
    'scoring_config' => ['count' => 5, 'emoji' => '🍎'],
    'points' => 10, 
    'order' => 1
]);

QuestionOption::create(['question_id' => $q1->id, 'text_value' => '3', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '4', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '5', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '6', 'is_correct' => false]);

$syncIds[] = $q1->id;

// Sync
$bank->assignedQuestions()->sync($syncIds);

echo "Created 1 count objects question.\n";
