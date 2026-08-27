<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mission;
use App\Models\QuizQuestion;
use App\Models\QuestionOption;
use App\Models\QuizType;

$mission = Mission::where('title', 'Mega Test Mission')->first();
$bank = $mission->questionBank;
$fillBlankType = QuizType::where('slug', 'fill-blank')->first();

// Create Question 1
$q1 = QuizQuestion::create([
    'quiz_bank_id' => $bank->id,
    'quiz_type_id' => $fillBlankType->id,
    'prompt' => "Fill in the missing letter:\nA _ P L E",
    'prompt_image_url' => 'https://em-content.zobj.net/source/apple/354/red-apple_1f34e.png',
    'points' => 10,
    'order' => 1,
]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => 'B', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => 'M', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q1->id, 'text_value' => 'P', 'is_correct' => true]);

// Create Question 2
$q2 = QuizQuestion::create([
    'quiz_bank_id' => $bank->id,
    'quiz_type_id' => $fillBlankType->id,
    'prompt' => "Fill in the missing letter:\nB A _ A N A",
    'prompt_image_url' => 'https://em-content.zobj.net/source/apple/354/banana_1f34c.png',
    'points' => 10,
    'order' => 2,
]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'N', 'is_correct' => true]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'T', 'is_correct' => false]);
QuestionOption::create(['question_id' => $q2->id, 'text_value' => 'R', 'is_correct' => false]);

// Sync ONLY fill-blank to mission
$bank->assignedQuestions()->sync([$q1->id, $q2->id]);

echo "Created and synced 2 fill-blank questions to the mission.\n";
