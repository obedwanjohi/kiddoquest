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
$patternSrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\pattern_apple_banana_1784476748688.png';
$destDir = __DIR__.'/public/images/questions';
copy($patternSrc, $destDir.'/mega_pattern.png');

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

// Question 8: Complete the Pattern (type 10)
$q8 = q($bankId, 10, 'Complete the pattern.', '/images/questions/mega_pattern.png');
opt($q8, 'Cherry', false, '/images/questions/mega_cherry.png');
opt($q8, 'Apple', false, '/images/questions/mega_apple.png');
opt($q8, 'Banana', true, '/images/questions/mega_banana.png');

// Question 9: Drag Sequence (type 5)
// Put the numbers 1, 2, 3 in order.
$q9 = q($bankId, 5, 'Sort the numbers 1, 2, 3.');
opt($q9, '1', false, null, '1', 0);
opt($q9, '2', false, null, '2', 1);
opt($q9, '3', false, null, '3', 2);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 9;
    $mission->save();
}

echo "Added 'Complete Pattern' and 'Drag Sequence' questions. Total questions: 9.\n";
