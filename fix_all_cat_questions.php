<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;
use App\Models\QuestionBank;
use Illuminate\Support\Facades\DB;

$qs = QuizQuestion::where('prompt', 'Choose the correct sentence')->get();
$bank = QuestionBank::where('name', 'Mega Test Bank')->first();

foreach ($qs as $q) {
    echo "Processing Q{$q->id}...\n";
    
    // If it has 3 options, let's fix it!
    if ($q->options->count() == 3) {
        $q->update([
            'prompt_image_url' => '/images/questions/mega_cat_sleeping.png'
        ]);
        
        $opts = $q->options;
        foreach ($opts as $idx => $o) {
            if ($idx == 0) $o->update(['text_value' => 'The cat is sleeping', 'is_correct' => true]);
            if ($idx == 1) $o->update(['text_value' => 'The dog is running', 'is_correct' => false]);
            if ($idx == 2) $o->update(['text_value' => 'The bird is flying', 'is_correct' => false]);
        }
        echo "Fixed Q{$q->id} with image and text!\n";
        
        // Ensure it's in the bank
        if ($bank && !$q->questionBanks->contains($bank->id)) {
            DB::table('question_bank_questions')->insert([
                'question_bank_id' => $bank->id,
                'question_id' => $q->id,
                'sort_order' => 10,
            ]);
        }
    } else {
        // If it doesn't have 3 options, remove it from Mega Test Bank to prevent empty screens
        if ($bank) {
            DB::table('question_bank_questions')
                ->where('question_bank_id', $bank->id)
                ->where('question_id', $q->id)
                ->delete();
            echo "Removed broken Q{$q->id} from Mega Test Bank.\n";
        }
    }
}
