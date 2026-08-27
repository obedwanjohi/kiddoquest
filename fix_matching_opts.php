<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuestionOption;

// Fix the matching options: right side options (odd sort_order) should be is_correct = 1
$updated = QuestionOption::whereHas('question', function($q) {
    $q->where('quiz_type_id', 3); // Matching questions
})->whereRaw('sort_order % 2 != 0')
  ->update(['is_correct' => 1]);

echo "Fixed is_correct for {$updated} matching options!\n";
