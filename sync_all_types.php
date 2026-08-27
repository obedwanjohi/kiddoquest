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
if (!$mission) die("Mission not found\n");
$bank = $mission->questionBank;

function getQt($slug, $name, $code) {
    return QuizType::firstOrCreate(['slug' => $slug], ['name' => $name, 'code' => $code]);
}

$qtMC = getQt('multiple-choice', 'Multiple Choice', 'QT-01');
$qtTF = getQt('true-false', 'True/False', 'QT-02');
$qtMatch = getQt('matching', 'Matching', 'QT-03');
$qtSort = getQt('drag-sort', 'Drag & Sort', 'QT-04');
$qtSeq = getQt('drag-sequence', 'Drag Sequence', 'QT-05');
$qtPattern = getQt('pattern', 'Complete the Pattern', 'QT-08');
$qtFB = getQt('fill-blank', 'Fill in the Blank', 'QT-09');
$qtCount = getQt('count-objects', 'Count Objects', 'QT-10');
$qtTracing = getQt('tracing', 'Tracing', 'QT-12');
$qtSpeak = getQt('speak-repeat', 'Speak & Repeat', 'QT-07');

// Remove old questions
$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$syncIds = [];
$order = 1;

function addQ($data) {
    global $bank, $syncIds, $order;
    $data['question_bank_id'] = $bank->id;
    $data['order'] = $order++;
    $data['points'] = 10;
    $q = QuizQuestion::create($data);
    $syncIds[] = $q->id;
    return $q;
}

// 1. Multiple Choice (Image options)
$q = addQ(['quiz_type_id' => $qtMC->id, 'prompt' => 'Which one is the Lion?']);
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/375/375051.png', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/4140/4140047.png', 'is_correct' => 1]); // Lion
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/10328/10328014.png', 'is_correct' => 0]);

// 2. True / False
$q = addQ(['quiz_type_id' => $qtTF->id, 'prompt' => 'Is the Sun hot?', 'prompt_image_url' => 'https://cdn-icons-png.flaticon.com/512/869/869869.png']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'True', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'False', 'is_correct' => 0]);

// 3. Fill in the Blank
$q = addQ(['quiz_type_id' => $qtFB->id, 'prompt' => 'A _ P L E', 'prompt_image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'P', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'M', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'B', 'is_correct' => 0]);

// 4. Drag Sequence
$q = addQ(['quiz_type_id' => $qtSeq->id, 'prompt' => 'Order the numbers from 1 to 4']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '1', 'order' => 1, 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '2', 'order' => 2, 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '3', 'order' => 3, 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '4', 'order' => 4, 'is_correct' => 1]);

// 5. Drag & Sort
$q = addQ(['quiz_type_id' => $qtSort->id, 'prompt' => 'Sort the animals!']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Cow', 'match_key' => 'Farm', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Pig', 'match_key' => 'Farm', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Lion', 'match_key' => 'Wild', 'is_correct' => 1]);

// 6. Matching
$q = addQ(['quiz_type_id' => $qtMatch->id, 'prompt' => 'Match the fruit to its color!']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Apple', 'match_key' => 'Red', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Banana', 'match_key' => 'Yellow', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Grape', 'match_key' => 'Purple', 'is_correct' => 1]);

// 7. Complete the Pattern
$q = addQ(['quiz_type_id' => $qtPattern->id, 'prompt' => 'Complete the pattern!']);
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png', 'order' => 1, 'is_correct' => 0]); 
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2904/2904886.png', 'order' => 2, 'is_correct' => 0]); 
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png', 'order' => 3, 'is_correct' => 0]); 
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2904/2904886.png', 'order' => 4, 'is_correct' => 1]); // Answer
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/2904/2904886.png', 'is_correct' => 1]); // Option: Correct
QuestionOption::create(['question_id' => $q->id, 'image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png', 'is_correct' => 0]); // Option: Wrong

// 8. Count Objects
$q = addQ(['quiz_type_id' => $qtCount->id, 'prompt' => 'Count the apples!', 'scoring_config' => ['count' => 3, 'emoji' => '🍎']]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '2', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '3', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '4', 'is_correct' => 0]);

// 9. Tracing
$q = addQ(['quiz_type_id' => $qtTracing->id, 'prompt' => 'Trace the letter A', 'metadata' => ['character' => 'A']]);

// 10. Speak & Repeat
$q = addQ(['quiz_type_id' => $qtSpeak->id, 'prompt' => 'Say the word!', 'metadata' => ['word' => 'Apple'], 'prompt_image_url' => 'https://cdn-icons-png.flaticon.com/512/415/415733.png']);

$bank->assignedQuestions()->sync($syncIds);

echo "Created " . count($syncIds) . " questions covering all 10 ported types!\n";
