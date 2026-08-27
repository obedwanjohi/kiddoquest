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
$cherrySrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\mc_cherry_1784476198760.png';
$destDir = __DIR__.'/public/images/questions';
copy($cherrySrc, $destDir.'/mega_cherry.png');

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

// Helpers
function q($bankId, $typeId, $prompt) {
    return QuizQuestion::create([
        'question_bank_id' => $bankId,
        'quiz_type_id'     => $typeId,
        'prompt'           => $prompt,
        'difficulty'       => 'easy',
        'points'           => 10,
        'sort_order'       => 0,
    ]);
}

function opt($q, $text, $correct, $imgUrl = null) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => $imgUrl ? 'image' : 'text',
        'text_value'   => $text,
        'image_url'    => $imgUrl,
        'is_correct'   => $correct,
    ]);
}

// Question 4: Multiple Choice (Text Options)
$q4 = q($bankId, 1, 'Select the number 5.');
opt($q4, '3', false);
opt($q4, '5', true);
opt($q4, '8', false);

// Question 5: Multiple Choice (Image Options)
$q5 = q($bankId, 1, 'Select the Apple.');
opt($q5, 'Cherry', false, '/images/questions/mega_cherry.png');
opt($q5, 'Banana', false, '/images/questions/mega_banana.png');
opt($q5, 'Apple', true, '/images/questions/mega_apple.png');

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 5;
    $mission->save();
}

echo "Added 'MC Text' and 'MC Image' questions. Total questions: 5.\n";
