<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(App\Models\Mission::all() as $m) {
    echo "ID: {$m->id} Title: {$m->title} QPS: {$m->questions_per_session}\n";
}
