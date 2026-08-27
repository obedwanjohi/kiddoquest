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
$patternType = QuizType::where('slug', 'pattern')->first();
if (!$patternType) {
    $patternType = QuizType::firstOrCreate(['slug' => 'pattern'], ['name' => 'Pattern', 'code' => 'QT-99']);
}

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];

$q1 = QuizQuestion::create([
    'question_bank_id' => $bank->id, 
    'quiz_type_id' => $patternType->id, 
    'prompt' => "What comes next?", 
    'prompt_image_url' => '/img/dummy-pattern.svg',
    'points' => 10, 
    'order' => 1
]);

QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🍌', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🍎', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🍓', 'is_correct' => false]);

$syncIds[] = $q1->id;

// Sync
$bank->assignedQuestions()->sync($syncIds);

echo "Created 1 pattern question.\n";
