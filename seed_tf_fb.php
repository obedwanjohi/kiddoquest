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

function opt($q, $text, $correct, $imgUrl = null) {
    QuestionOption::create([
        'question_id'  => $q->id,
        'content_type' => $imgUrl ? 'image' : 'text',
        'text_value'   => $text,
        'image_url'    => $imgUrl,
        'is_correct'   => $correct,
    ]);
}

// Question 6: True / False
$q6 = q($bankId, 2, 'Is this an Apple?', '/images/questions/mega_apple.png');
opt($q6, 'Yes', true);
opt($q6, 'No', false);

// Question 7: Fill the Blank
$q7 = q($bankId, 8, 'Fill in the missing letter: A _ P L E', '/images/questions/mega_apple.png');
opt($q7, 'B', false);
opt($q7, 'M', false);
opt($q7, 'P', true);

// Update Mission question count
$mission = Mission::where('slug', 'mega-test-mission')->first();
if ($mission) {
    $mission->questions_per_session = 7;
    $mission->save();
}

echo "Added 'True/False' and 'Fill the Blank' questions. Total questions: 7.\n";
