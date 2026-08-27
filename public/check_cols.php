<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/plain');
echo "=== Adventure Worlds Columns ===\n";
$cols = \Illuminate\Support\Facades\Schema::getColumnListing('adventure_worlds');
print_r($cols);

echo "\n=== All Subjects in DB ===\n";
$subjects = \App\Models\Subject::all();
foreach ($subjects as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | Code: {$s->code}\n";
}
