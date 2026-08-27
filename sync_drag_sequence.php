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
$dragSeqType = QuizType::where('slug', 'drag-sequence')->first();

if (!$dragSeqType) {
    die("drag-sequence type not found\n");
}

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

// Create Drag Sequence Question
$q1 = QuizQuestion::create([
    'question_bank_id' => $bank->id,
    'quiz_type_id' => $dragSeqType->id,
    'prompt' => "Drag the shapes to their matching outlines",
    'points' => 10,
    'order' => 1,
]);

// Options are the cards that will be shuffled into the tray
// In drag-sequence, the correct order is defined by the option order (0, 1, 2).
// In the mockup: Star, Circle, Square
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '⭐', 'is_correct' => true, 'sort_order' => 0]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🔵', 'is_correct' => true, 'sort_order' => 1]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => '🟥', 'is_correct' => true, 'sort_order' => 2]);

// Sync ONLY this question to mission
$bank->assignedQuestions()->sync([$q1->id]);

echo "Created and synced 1 drag-sequence question to the mission.\n";
