<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;

$q = QuizQuestion::find(251);
if ($q) {
    $q->update([
        'prompt_image_url' => '/images/questions/mega_cat_sleeping.png'
    ]);
    echo "Successfully updated Question 251 with the new cat image!\n";
} else {
    echo "Question 251 not found!\n";
}
