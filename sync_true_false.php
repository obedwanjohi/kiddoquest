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
$tfType = QuizType::where('slug', 'true-false')->first();

if (!$tfType) die("true-false type not found\n");

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];

// 1. False answer
$q1 = QuizQuestion::create(['question_bank_id' => $bank->id, 'quiz_type_id' => $tfType->id, 'prompt' => "Lions love to eat vegetables.", 'points' => 10, 'order' => 1]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => 'True', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => 'False', 'is_correct' => true]);
$syncIds[] = $q1->id;

// 2. True answer (with image!)
// Helper to create SVG data URI so we can test the real <img> tag without broken links
function makeEmojiImg($emoji) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-size="80">'.$emoji.'</text></svg>';
    return 'data:image/svg+xml;base64,'.base64_encode($svg);
}

$q2 = QuizQuestion::create([
    'question_bank_id' => $bank->id, 
    'quiz_type_id' => $tfType->id, 
    'prompt' => "This is an apple.", 
    'prompt_image_url' => makeEmojiImg('🍌'), // Showing a banana!
    'points' => 10, 
    'order' => 2
]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'True', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'False', 'is_correct' => true]);
$syncIds[] = $q2->id;

// Sync
$bank->assignedQuestions()->sync($syncIds);

echo "Created 2 true/false questions.\n";
