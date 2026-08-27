<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;
use App\Models\QuestionOption;

$q = QuizQuestion::where('prompt', 'Choose the correct sentence')->first();
if ($q) {
    $q->update([
        'prompt_image_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b6/Felis_catus-cat_on_snow.jpg/640px-Felis_catus-cat_on_snow.jpg'
    ]);
    
    // update options
    $opts = $q->options;
    foreach ($opts as $idx => $o) {
        if ($idx == 0) $o->update(['text_value' => 'The cat is sleeping']);
        if ($idx == 1) $o->update(['text_value' => 'The dog is running']);
        if ($idx == 2) $o->update(['text_value' => 'The bird is flying']);
    }
    echo "Fixed question and options!";
} else {
    echo "Question not found!";
}
