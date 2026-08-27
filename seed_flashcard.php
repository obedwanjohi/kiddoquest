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

$flashcardType = QuizType::firstOrCreate(
    ['slug' => 'flashcard'],
    [
        'name' => 'Flashcard',
        'code' => 'QT-14',
        'description' => 'Learn a word and flip the card',
        'icon' => 'dY?', // Or emoji
        'is_active' => true,
    ]
);

// Check if we already have this mockup to prevent duplicates
$q = QuizQuestion::where('prompt', 'Tap the card to learn the word!')->first();
if (!$q) {
    $q = QuizQuestion::create([
        'quiz_type_id' => $flashcardType->id,
        'prompt' => 'Tap the card to learn the word!',
        'points' => 1,
    ]);

    QuestionOption::create([
        'question_id' => $q->id,
        'text_value' => 'Apple',
        'image_url' => '/images/questions/mega_apple.png',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
}

if (!$q->questionBanks->contains($bank->id)) {
    DB::table('question_bank_questions')->insert([
        'question_bank_id' => $bank->id,
        'question_id' => $q->id,
        'sort_order' => 20, // Put it after the multiple choice
    ]);
}

// Make sure questions_per_session is 10 for the mission
$mission = App\Models\Mission::where('title', 'Mega Test Mission')->first();
if ($mission && $mission->questions_per_session < 10) {
    $mission->update(['questions_per_session' => 10]);
}

echo "Successfully seeded the 3D Flashcard question!\n";
