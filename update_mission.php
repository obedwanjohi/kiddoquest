<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$m = App\Models\Mission::where('title', 'Mega Test Mission')->first();
if ($m) {
    $m->questions_per_session = 20;
    $m->save();
    echo "Updated Mega Test Mission to serve 20 questions.\n";
} else {
    echo "Mission not found.\n";
}
