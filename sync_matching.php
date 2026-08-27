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
$matchType = QuizType::where('slug', 'matching')->first();

if (!$matchType) die("matching type not found\n");

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];

// Helper to create SVG data URI so we can test the real <img> tag without broken links
function makeEmojiImg($emoji) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" font-size="80">'.$emoji.'</text></svg>';
    return 'data:image/svg+xml;base64,'.base64_encode($svg);
}

// 1. Image to Image
$q1 = QuizQuestion::create(['question_bank_id' => $bank->id, 'quiz_type_id' => $matchType->id, 'prompt' => "Match the same pictures!", 'points' => 10, 'order' => 1]);
QuestionOption::create(['question_id' => $q1->id, 'image_url' => makeEmojiImg('🍎'), 'match_key' => 'apple', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'image_url' => makeEmojiImg('🍌'), 'match_key' => 'banana', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'image_url' => makeEmojiImg('🍎'), 'match_key' => 'apple', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q1->id, 'image_url' => makeEmojiImg('🍌'), 'match_key' => 'banana', 'is_correct' => true]);
$syncIds[] = $q1->id;

// 2. Text to Image
$q2 = QuizQuestion::create(['question_bank_id' => $bank->id, 'quiz_type_id' => $matchType->id, 'prompt' => "Match the word to the picture!", 'points' => 10, 'order' => 2]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'Apple', 'match_key' => 'apple', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'Banana', 'match_key' => 'banana', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q2->id, 'image_url' => makeEmojiImg('🍎'), 'match_key' => 'apple', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q2->id, 'image_url' => makeEmojiImg('🍌'), 'match_key' => 'banana', 'is_correct' => true]);
$syncIds[] = $q2->id;

// 3. Text to Text
$q3 = QuizQuestion::create(['question_bank_id' => $bank->id, 'quiz_type_id' => $matchType->id, 'prompt' => "Match uppercase to lowercase!", 'points' => 10, 'order' => 3]);
QuestionOption::create(['question_id' => $q3->id, 'text_value' => 'A', 'match_key' => 'A', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q3->id, 'text_value' => 'B', 'match_key' => 'B', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q3->id, 'text_value' => 'a', 'match_key' => 'A', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q3->id, 'text_value' => 'b', 'match_key' => 'B', 'is_correct' => true]);
$syncIds[] = $q3->id;

// Sync
$bank->assignedQuestions()->sync($syncIds);

echo "Created 3 matching questions.\n";
