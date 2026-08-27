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

if ($bank) {
    // 9. Fill in the Blank
    $q9 = QuizQuestion::create([
        'question_bank_id' => $bank->id,
        'quiz_type_id'     => 8, // QT_FILL
        'prompt'           => 'Fill in the missing letter: A _ P L E',
        'prompt_image_url' => '/images/questions/match_apple_05.png',
        'difficulty'       => 'easy',
        'points'           => 10,
        'sort_order'       => 0,
    ]);

    QuestionOption::create([
        'question_id'  => $q9->id,
        'content_type' => 'text',
        'text_value'   => 'P',
        'is_correct'   => true,
    ]);
    
    // Update mission question count
    $mission = Mission::where('slug', 'mega-test-mission')->first();
    if ($mission) {
        $mission->questions_per_session = 9;
        $mission->save();
    }
    
    echo "Added Fill-in-the-Blank question and updated mission to 9 questions!\n";
} else {
    echo "Mega Test Bank not found!\n";
}
