<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$bubbly = [
    '/storage/media/images/jCD530bmTjVscNSbnSMl7pCfybBODgtUlp9K8zMH.png', // 1
    '/storage/media/images/195uZwn1QZSTbx6sx8lYrEkvzIPqCfBFZSfL3zKV.png', // 2
    '/storage/media/images/92aXb0cVRSinScuAVW5rot5HkMp1AlMXitP20zCa.png', // 3
    '/storage/media/images/RxN7KbzGjgwEn1b3oN2ZsxZfVQLXYCCfvATkhAXO.png', // 4
    '/storage/media/images/XJUyK9usaeUnaF2yBO4P1lg5i7nOXQnH7yUdC3mX.png'  // 5
];

$affected = DB::table('question_options')
  ->whereNotNull('image_url')
  ->whereNotIn('image_url', $bubbly)
  ->whereRaw('text_value REGEXP "^[0-9]+$"')
  ->update(['image_url' => null]);

echo "Restored $affected numeric options back to plain digits.\n";
