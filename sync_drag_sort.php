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
$dragSortType = QuizType::where('slug', 'drag-sort')->first();

if (!$dragSortType) {
    die("drag-sort type not found\n");
}

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

// Create Drag Sort Question
$q1 = QuizQuestion::create([
    'question_bank_id' => $bank->id,
    'quiz_type_id' => $dragSortType->id,
    'prompt' => "Sort the foods into the correct baskets",
    'points' => 10,
    'order' => 1,
]);

// Options are the cards that will be shuffled into the tray
// match_key is the bucket category
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🍎', 'match_key' => 'Fruits', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🍌', 'match_key' => 'Fruits', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🥕', 'match_key' => 'Vegetables', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🥦', 'match_key' => 'Vegetables', 'is_correct' => true]);

// Sync ONLY this question to mission
$bank->assignedQuestions()->sync([$q1->id]);

echo "Created and synced 1 drag-sort question to the mission.\n";
