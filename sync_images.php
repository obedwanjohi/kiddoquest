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
        $map[$val] = $o->image_url;
    }
}

$updated = 0;
foreach($map as $text => $url) {
    // get all options that exactly match the string
    $targets = DB::table('question_options')
        ->whereNull('image_url')
        ->get();
        
    foreach ($targets as $t) {
        if (trim($t->text_value) === (string)$text) {
            DB::table('question_options')
                ->where('id', $t->id)
                ->update(['image_url' => $url]);
            $updated++;
        }
    }
}

echo "Synced $updated missing images for numbers!\n";
