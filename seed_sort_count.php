<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\Mission;

// 1. Copy image
$countSrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\count_3_apples_1784476922091.png';
$destDir = __DIR__.'/public/images/questions';
copy($countSrc, $destDir.'/mega_count_apples.png');

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

// Helpers
function q($bankId, $typeId, $prompt, $promptImage = null) {
    return QuizQuestion::create([
        'question_bank_id' => $bankId,
        'quiz_type_id'     => $typeId,
        'prompt'           => $prompt,
        'prompt_image_url' => $promptImage,
        'difficulty'       => 'easy',
        'points'           => 10,
        'sort_order'       => 0,
    ]);
}

function opt($q, $text, $correct, $imgUrl = null, $matchKey = null, $sortOrder = 0) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => $imgUrl ? 'image' : 'text',
        'text_value'   => $text,
        'image_url'    => $imgUrl,
        'is_correct'   => $correct,
        'match_key'    => $matchKey,
        'sort_order'   => $sortOrder,
    ]);
}

// Question 10: Drag & Sort (type 4)
// Categories: "Fruits" and "Numbers"
$q10 = q($bankId, 4, 'Sort into Fruits and Numbers.');
opt($q10, 'Apple', true, '/images/questions/mega_apple.png', 'Fruits', 0);
opt($q10, 'Banana', true, '/images/questions/mega_banana.png', 'Fruits', 1);
opt($q10, '1', true, null, 'Numbers', 2);
opt($q10, '2', true, null, 'Numbers', 3);

// Question 11: Count the Objects (type 9)
$q11 = q($bankId, 9, 'How many apples?', '/images/questions/mega_count_apples.png');
opt($q11, '1', false);
opt($q11, '2', false);
opt($q11, '3', true);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 11;
    $mission->save();
}

echo "Added 'Drag & Sort' and 'Count Objects' questions. Total questions: 11.\n";
