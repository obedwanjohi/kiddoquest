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

function opt($q, $text, $correct, $imgUrl, $matchKey, $sortOrder) {
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

function pairOpts($q, $pairs) {
    foreach ($pairs as $i => $p) {
        // Left (Even, false)
        opt($q, $p['left'], false, $p['leftImg'] ?? null, $p['key'], ($i * 2));
        // Right (Odd, true)
        opt($q, $p['right'], true, $p['rightImg'] ?? null, $p['key'], ($i * 2) + 1);
    }
}

// Question 2: Match (Text to Text)
$q2 = q($bankId, 3, 'Match the identical numbers.');
pairOpts($q2, [
    ['left' => 'One', 'right' => '1', 'key' => '1'],
    ['left' => 'Two', 'right' => '2', 'key' => '2']
]);

// Question 3: Match (Text to Image)
$q3 = q($bankId, 3, 'Match the word to the picture.');
pairOpts($q3, [
    ['left' => 'Apple', 'right' => 'Apple Picture', 'rightImg' => '/images/questions/mega_apple.png', 'key' => 'apple'],
    ['left' => 'Banana', 'right' => 'Banana Picture', 'rightImg' => '/images/questions/mega_banana.png', 'key' => 'banana']
]);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 3;
    $mission->save();
}

echo "Added 'Text to Text' and 'Text to Image' matching questions. Total questions: 3.\n";
