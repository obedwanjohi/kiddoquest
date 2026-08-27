<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$recent = DB::table('question_options')
    ->where('updated_at', '>=', now()->subMinutes(15))
    ->get(['id', 'text_value', 'image_url', 'updated_at']);

echo json_encode($recent, JSON_PRETTY_PRINT);
