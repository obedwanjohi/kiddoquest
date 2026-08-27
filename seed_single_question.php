<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\Mission;

// 1. Copy images
$appleSrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\match_apple_1784475748839.png';
$bananaSrc = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\match_banana_1784475778545.png';

$destDir = __DIR__.'/public/images/questions';
if (!is_dir($destDir)) {
    mkdir($destDir, 0777, true);
}

copy($appleSrc, $destDir.'/mega_apple.png');
copy($bananaSrc, $destDir.'/mega_banana.png');

// 2. Wipe bank
$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
if (!$bank) die("Bank not found\n");
$bankId = $bank->id;

// Delete all existing questions in this bank
QuizQuestion::where('question_bank_id', $bankId)->forceDelete();

// 3. Helpers
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
        // Left (Even)
        opt($q, $p['left'], false, $p['leftImg'] ?? null, $p['key'], ($i * 2));
        // Right (Odd)
        opt($q, $p['right'], true, $p['rightImg'] ?? null, $p['key'], ($i * 2) + 1);
    }
}

// 4. Seed Question 1: Matching Image to Image
$q1 = q($bankId, 3, 'Match the identical images.');
pairOpts($q1, [
    ['left' => 'Apple', 'leftImg' => '/images/questions/mega_apple.png', 'right' => 'Apple', 'rightImg' => '/images/questions/mega_apple.png', 'key' => 'apple'],
    ['left' => 'Banana', 'leftImg' => '/images/questions/mega_banana.png', 'right' => 'Banana', 'rightImg' => '/images/questions/mega_banana.png', 'key' => 'banana']
]);

// 5. Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 1;
    $mission->save();
}

echo "Bank wiped. Seeded exactly ONE question (Image Matching).\n";
