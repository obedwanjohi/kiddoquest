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
$fillBlankType = QuizType::firstOrCreate(
    ['slug' => 'fill-blank'],
    [
        'name' => 'Fill in the Blank',
        'code' => 'QT-08',
        'description' => 'Complete the word',
        'icon' => 'A_Z',
        'is_active' => true,
    ]
);

// Delete the old "Fill in the missing letter: A _ P L E" question if it exists to avoid duplicates
QuizQuestion::where('prompt', 'Fill in the missing letter: A _ P L E')->delete();

$q = QuizQuestion::where('prompt', 'Fill in the missing letter!')->first();
if (!$q) {
    $q = QuizQuestion::create([
        'quiz_type_id' => $fillBlankType->id,
        'prompt' => 'Fill in the missing letter!',
        'prompt_image_url' => '/images/questions/mega_apple.png',
        'points' => 1,
        'metadata' => [
            'puzzle' => ['A', '_', 'P', 'L', 'E']
        ]
    ]);

    QuestionOption::create([
        'question_id' => $q->id,
        'text_value' => 'P',
        'is_correct' => true,
        'sort_order' => 1,
    ]);
    QuestionOption::create([
        'question_id' => $q->id,
        'text_value' => 'M',
        'is_correct' => false,
        'sort_order' => 2,
    ]);
    QuestionOption::create([
        'question_id' => $q->id,
        'text_value' => 'B',
        'is_correct' => false,
        'sort_order' => 3,
    ]);
}

if (!$q->questionBanks->contains($bank->id)) {
    DB::table('question_bank_questions')->insert([
        'question_bank_id' => $bank->id,
        'question_id' => $q->id,
        'sort_order' => 30, // Put it next
    ]);
}

echo "Successfully seeded the Fill in the Blank question!\n";
