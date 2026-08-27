<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;
use App\Models\QuestionBank;
use Illuminate\Support\Facades\DB;

$bank = QuestionBank::where('name', 'Mega Test Bank')->first();

if ($bank) {
    // Find flashcard question and delete it from the bank
    $q = QuizQuestion::where('prompt', 'Tap the card to learn the word!')->first();
    if ($q) {
        DB::table('question_bank_questions')
            ->where('question_bank_id', $bank->id)
            ->where('question_id', $q->id)
            ->delete();
        echo "Removed Flashcard question from Mega Test Bank.\n";
    }
}
