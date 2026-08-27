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

$questions = QuizQuestion::where('question_bank_id', $bank->id)->get();
foreach ($questions as $q) {
    QuestionOption::where('question_id', $q->id)->delete();
    $q->delete();
}

$type = QuizType::firstOrCreate(['slug' => 'fill-blank'], ['name' => 'Fill in the Blank', 'code' => 'QT-09']);

$q = QuizQuestion::create([
    'question_bank_id' => $bank->id,
    'quiz_type_id' => $type->id,
    'prompt' => 'A _ P L E',
    'prompt_image_url' => '/images/test_assets/apple.png',
    'points' => 10,
    'order' => 1
]);

QuestionOption::create(['question_id' => $q->id, 'text_value' => 'P', 'is_correct' => 1]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'M', 'is_correct' => 0]);
QuestionOption::create(['question_id' => $q->id, 'text_value' => 'B', 'is_correct' => 0]);

$bank->assignedQuestions()->sync([$q->id]);

echo "Seeded 3: Fill in the Blank\n";
