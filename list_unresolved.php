<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$qImages = DB::table('quiz_questions')->where('prompt_image_url', 'like', '%/images/%')->pluck('prompt_image_url')->toArray();
$oImages = DB::table('question_options')->where('image_url', 'like', '%/images/%')->pluck('image_url')->toArray();
echo json_encode(array_values(array_unique(array_merge($qImages, $oImages))), JSON_PRETTY_PRINT);
