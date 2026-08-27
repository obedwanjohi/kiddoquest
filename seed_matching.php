<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;
use App\Models\QuestionBank;
use App\Models\QuizType;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\DB;

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();
$matchingType = QuizType::firstOrCreate(
    ['slug' => 'matching'],
    [
        'name' => 'Matching',
        'code' => 'QT-03',
        'description' => 'Match items',
        'icon' => '🔗',
        'is_active' => true,
    ]
);

$q = QuizQuestion::where('prompt', 'Match the animals to their names!')->first();
if (!$q) {
    $q = QuizQuestion::create([
        'quiz_type_id' => $matchingType->id,
        'prompt' => 'Match the animals to their names!',
        'points' => 1,
    ]);

    // Left Side (is_correct = false)
    QuestionOption::create(['question_id' => $q->id, 'text_value' => '🐶', 'match_key' => 'dog', 'is_correct' => false, 'sort_order' => 1]);
    QuestionOption::create(['question_id' => $q->id, 'text_value' => '🐱', 'match_key' => 'cat', 'is_correct' => false, 'sort_order' => 2]);
    QuestionOption::create(['question_id' => $q->id, 'text_value' => '🐮', 'match_key' => 'cow', 'is_correct' => false, 'sort_order' => 3]);

    // Right Side (is_correct = true)
    QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Dog', 'match_key' => 'dog', 'is_correct' => true, 'sort_order' => 4]);
    QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Cat', 'match_key' => 'cat', 'is_correct' => true, 'sort_order' => 5]);
    QuestionOption::create(['question_id' => $q->id, 'text_value' => 'Cow', 'match_key' => 'cow', 'is_correct' => true, 'sort_order' => 6]);
}

if (!$q->questionBanks->contains($bank->id)) {
    DB::table('question_bank_questions')->insert([
        'question_bank_id' => $bank->id,
        'question_id' => $q->id,
        'sort_order' => 40,
    ]);
}

echo "Successfully seeded the Matching question!\n";
