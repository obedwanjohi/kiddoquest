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

$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$type = QuizType::firstOrCreate(['slug' => 'multiple-choice'], ['name' => 'Multiple Choice', 'code' => 'QT-01']);

$q = QuizQuestion::create([
    'question_bank_id' => $bank->id,
    'quiz_type_id' => $type->id,
    'prompt' => 'Which one is the Lion?',
    'points' => 10,
    'order' => 1
]);

QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/elephant.png', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/lion.png', 'is_correct' => 1]); // Lion
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/tiger.png', 'is_correct' => 0]);

$bank->assignedQuestions()->sync([$q->id]);

echo "Seeded 1: Multiple Choice\n";
