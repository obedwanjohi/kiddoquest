<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\Mission;

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

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

function opt($q, $text, $imgUrl, $matchKey, $sortOrder) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => $imgUrl ? 'image' : 'text',
        'text_value'   => $text,
        'image_url'    => $imgUrl,
        'is_correct'   => false,
        'match_key'    => $matchKey,
        'sort_order'   => $sortOrder,
    ]);
}

// Question 12: Memory Match (type 11)
$q12 = q($bankId, 11, 'Match the pairs!');
opt($q12, 'Apple', '/images/questions/mega_apple.png', '1', 1);
opt($q12, 'Banana', '/images/questions/mega_banana.png', '2', 2);
opt($q12, 'Cherry', '/images/questions/mega_cherry.png', '3', 3);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 12;
    $mission->save();
}

echo "Added 'Memory Match' question. Total questions: 12.\n";
