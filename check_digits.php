<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$map = [];
$options = DB::table('question_options')->whereNotNull('image_url')->get();

foreach($options as $o) {
    $val = trim($o->text_value);
    if (is_numeric($val)) {
        if (!isset($map[$val])) $map[$val] = [];
        if (!in_array($o->image_url, $map[$val])) {
            $map[$val][] = $o->image_url;
        }
    }
}

echo json_encode($map, JSON_PRETTY_PRINT);
