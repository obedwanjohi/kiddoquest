<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

// Clear bad questions
QuizQuestion::where('question_bank_id', $bankId)->delete();

// Helpers
function q($bankId, $typeId, $prompt, $imageUrl = null) {
    return QuizQuestion::create([
        'question_bank_id' => $bankId,
        'quiz_type_id'     => $typeId,
        'prompt'           => $prompt,
        'prompt_image_url' => $imageUrl,
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
function pairOpts($q, $pairs) {
    foreach ($pairs as $i => $p) {
        // Left (Even)
        opt($q, $p['left'], false, $p['leftImg'] ?? null, $p['key'], ($i * 2));
        // Right (Odd)
        opt($q, $p['right'], false, $p['rightImg'] ?? null, $p['key'], ($i * 2) + 1);
    }
}
function seqOpts($q, $items) {
    foreach ($items as $item) {
        opt($q, $item['text'], false, $item['img'] ?? null, (string)$item['order'], 0);
    }
}

// 1. Multiple Choice (Text Options)
$q1 = q($bankId, 1, 'Select the number 5.');
opt($q1, '3', false); opt($q1, '5', true); opt($q1, '8', false);

// 2. Multiple Choice (Image Options)
$q2 = q($bankId, 1, 'Select the Apple.');
opt($q2, 'Banana', false, '/images/questions/match_banana.png');
opt($q2, 'Apple', true, '/images/questions/match_apple_05.png');

// 3. True/False
$q3 = q($bankId, 2, 'Is this an Apple?', '/images/questions/match_apple_05.png');
opt($q3, 'True', true); opt($q3, 'False', false);

// 4. Match (Text to Text)
$q4 = q($bankId, 3, 'Match the identical numbers.');
pairOpts($q4, [
    ['left' => 'One', 'right' => '1', 'key' => '1'],
    ['left' => 'Two', 'right' => '2', 'key' => '2']
]);

// 5. Match (Image to Image)
$q5 = q($bankId, 3, 'Match the identical images.');
pairOpts($q5, [
    ['left' => 'Apple', 'leftImg' => '/images/questions/match_apple_05.png', 'right' => 'Apple2', 'rightImg' => '/images/questions/match_apple_05.png', 'key' => 'apple'],
    ['left' => 'Banana', 'leftImg' => '/images/questions/match_banana.png', 'right' => 'Banana2', 'rightImg' => '/images/questions/match_banana.png', 'key' => 'banana']
]);

// 6. Match (Text to Image)
$q6 = q($bankId, 3, 'Match text to image.');
pairOpts($q6, [
    ['left' => 'Apple', 'right' => 'AppleImg', 'rightImg' => '/images/questions/match_apple_05.png', 'key' => 'apple'],
    ['left' => 'Banana', 'right' => 'BananaImg', 'rightImg' => '/images/questions/match_banana.png', 'key' => 'banana']
]);

// 7. Drag Sequence (5) instead of Sort (4)
$q7 = q($bankId, 5, 'Sort the numbers 1, 2, 3.');
seqOpts($q7, [
    ['text' => '2', 'order' => 2],
    ['text' => '1', 'order' => 1],
    ['text' => '3', 'order' => 3],
]);

// 8. Pattern
$q8 = q($bankId, 10, 'Complete the pattern.', '/images/questions/pattern_star_heart.png');
opt($q8, 'Heart', false, '/images/questions/pat_heart.png');
opt($q8, 'Star', true, '/images/questions/pat_star.png');

// 9. Fill in the Blank
$q9 = q($bankId, 8, 'Fill in the missing letter: A _ P L E', '/images/questions/match_apple_05.png');
opt($q9, 'P', true);

echo "Mega Test Mission data fixed perfectly!\n";
