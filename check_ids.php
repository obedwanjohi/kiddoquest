<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$options = DB::table('question_options')->whereIn('text_value', ['6','7','8','9','10'])->whereNotNull('image_url')->get();
$map = [];
foreach($options as $o) {
    $map[$o->id] = $o->image_url;
}
echo json_encode($map, JSON_PRETTY_PRINT);
