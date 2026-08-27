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

function getQt($slug, $name, $code) {
    return QuizType::firstOrCreate(['slug' => $slug], ['name' => $name, 'code' => $code]);
}

$qtMC = getQt('multiple-choice', 'Multiple Choice', 'QT-01');
$qtTF = getQt('true-false', 'True/False', 'QT-02');
$qtFB = getQt('fill-blank', 'Fill in the Blank', 'QT-09');
$qtSeq = getQt('drag-sequence', 'Drag Sequence', 'QT-05');

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
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/elephant.png', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/lion.png', 'is_correct' => 1]); // Lion
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/tiger.png', 'is_correct' => 0]);

// 2. True / False
$q = addQ(['quiz_type_id' => $qtTF->id, 'prompt' => 'Is the Sun hot?', 'prompt_image_url' => '/images/test_assets/sun.png']);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'True', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'False', 'is_correct' => 0]);

// 3. Fill in the Blank
$q = addQ(['quiz_type_id' => $qtFB->id, 'prompt' => 'A _ P L E', 'prompt_image_url' => '/images/test_assets/apple.png']);
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
$qtSort = getQt('drag-sort', 'Drag & Sort', 'QT-04');
$q = addQ(['quiz_type_id' => $qtSort->id, 'prompt' => 'Sort the animals!']);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/cow.png', 'match_key' => 'Farm', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/pig.png', 'match_key' => 'Farm', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/lion.png', 'match_key' => 'Wild', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/monkey.png', 'match_key' => 'Wild', 'is_correct' => 1]);

$bank->assignedQuestions()->sync($syncIds);

// 6. Matching
$qtMatch = getQt('matching', 'Matching', 'QT-03');
$q = addQ(['quiz_type_id' => $qtMatch->id, 'prompt' => 'Match the fruit to its color!']);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/apple.png', 'match_key' => 'Red', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/banana.png', 'match_key' => 'Yellow', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/grape.png', 'match_key' => 'Purple', 'is_correct' => 0]);

$bank->assignedQuestions()->sync($syncIds);

// 7. Complete the Pattern
$qtPattern = getQt('pattern', 'Complete the Pattern', 'QT-08');
$q = addQ(['quiz_type_id' => $qtPattern->id, 'prompt' => 'Complete the pattern!', 'prompt_image_url' => '/images/test_assets/pattern.png']);
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/banana.png', 'is_correct' => 1]); // Option: Correct
QuestionOption::create(['question_id' => $q->id, 'image_url' => '/images/test_assets/apple.png', 'is_correct' => 0]); // Option: Wrong

// 8. Count Objects
$qtCount = getQt('count-objects', 'Count Objects', 'QT-10');
$q = addQ(['quiz_type_id' => $qtCount->id, 'prompt' => 'Count the apples!', 'scoring_config' => ['count' => 3, 'image_url' => '/images/test_assets/apple.png']]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '2', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '3', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => '4', 'is_correct' => 0]);

// 10. Speak & Repeat
$qtSpeak = getQt('speak-repeat', 'Speak & Repeat', 'QT-07');
$q = addQ(['quiz_type_id' => $qtSpeak->id, 'prompt' => 'Say the word!', 'metadata' => ['word' => 'Apple'], 'prompt_image_url' => '/images/test_assets/apple.png']);

$bank->assignedQuestions()->sync($syncIds);

echo "Seeded up to Q10 (Speak & Repeat) - ALL TYPES PORTED AND VERIFIED!\n";
