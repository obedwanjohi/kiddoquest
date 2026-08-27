<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\Mission;

// 1. Copy cat image
$catSrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\cat_sleeping_1784527275913.png';
$destDir = __DIR__.'/public/images/questions';
copy($catSrc, $destDir.'/mega_cat.png');

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

// Helpers
function q($bankId, $typeId, $prompt, $imageUrl = null) {
    return QuizQuestion::create([
        'question_bank_id' => $bankId,
        'quiz_type_id'     => $typeId,
        'prompt'           => $prompt,
        'image_url'        => $imageUrl,
        'difficulty'       => 'medium',
        'points'           => 10,
        'sort_order'       => 0,
    ]);
}

function opt($q, $text, $correct) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => 'text',
        'text_value'   => $text,
        'is_correct'   => $correct,
    ]);
}

// Question 8: Comprehension Mode (Word Problem)
$q8 = q($bankId, 1, "Peter has 8 pencils.\nHe gives 3 pencils to his friend.\n\nHow many pencils does Peter have left?");
opt($q8, '4', false);
opt($q8, '5', true);
opt($q8, '6', false);
opt($q8, '7', false);

// Question 9: Reading Mode (Sentence Selection)
$q9 = q($bankId, 1, 'Choose the correct sentence.', '/images/questions/mega_cat.png');
opt($q9, 'The dog is running.', false);
opt($q9, 'The cat is sleeping.', true);
opt($q9, 'The bird is flying.', false);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    // Current is 7 questions, let's bump it to 9
    $mission->questions_per_session = 9;
    $mission->save();
}

echo "Added Comprehension and Reading mode questions. Total questions: 9.\n";
