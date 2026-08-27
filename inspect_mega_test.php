<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mission;

$mission = Mission::where('title', 'Mega Test Mission')->first();

if (!$mission) {
    echo "Mega Test Mission not found.\n";
    exit;
}

$bank = $mission->questionBank;
if (!$bank) {
    echo "No Question Bank linked.\n";
    exit;
}

echo "Mission: " . $mission->title . "\n";
echo "Bank: " . $bank->name . "\n";
echo "Total questions: " . $bank->questions()->count() . "\n\n";

foreach ($bank->questions as $i => $q) {
    echo "Q" . ($i + 1) . " [ID: {$q->id}] (Type: " . ($q->quizType ? $q->quizType->slug : 'None') . ")\n";
    echo "  Prompt: " . $q->prompt . "\n";
    echo "  Image: " . ($q->prompt_image_url ?: 'MISSING') . "\n";
    echo "  Options (" . $q->options->count() . "):\n";
    
    foreach ($q->options as $j => $opt) {
        $text = $opt->text_value ?: 'MISSING_TEXT';
        $img = $opt->image_url ?: 'NO_IMAGE';
        $correct = $opt->is_correct ? '(CORRECT)' : '';
        echo "    - Option " . ($j + 1) . " [ID: {$opt->id}]: Text='{$text}', Img='{$img}' {$correct}\n";
    }
    echo "--------------------------------------------------\n";
}
