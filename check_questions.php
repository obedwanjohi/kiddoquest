<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\QuizQuestion;

$qs = QuizQuestion::where('prompt', 'Choose the correct sentence')->get();
foreach ($qs as $q) {
    echo "ID: {$q->id}\n";
    echo "Prompt: {$q->prompt}\n";
    echo "Image: {$q->prompt_image_url}\n";
    echo "Options:\n";
    foreach ($q->options as $o) {
        echo " - text_value: {$o->text_value}\n";
    }
    echo "Banks: " . $q->questionBanks->pluck('name')->implode(', ') . "\n\n";
}
