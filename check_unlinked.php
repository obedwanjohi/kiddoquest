<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$media = DB::table('media')->pluck('name')->toArray();
$questions = DB::table('quiz_questions')->whereNotNull('prompt_image_url')->pluck('prompt_image_url')->toArray();
$options = DB::table('question_options')->whereNotNull('image_url')->pluck('image_url')->toArray();
$allUsed = array_merge($questions, $options);

$unlinked = [];
foreach ($media as $m) {
    $found = false;
    foreach ($allUsed as $u) {
        if (str_contains($u, $m)) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $unlinked[] = $m;
    }
}
echo json_encode($unlinked, JSON_PRETTY_PRINT);
