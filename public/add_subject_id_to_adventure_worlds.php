<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

header('Content-Type: text/plain');
echo "=== Migrating Database: Adding subject_id to adventure_worlds ===\n";

if (!Schema::hasColumn('adventure_worlds', 'subject_id')) {
    Schema::table('adventure_worlds', function (Blueprint $table) {
        $table->unsignedBigInteger('subject_id')->nullable()->after('description');
    });
    echo "SUCCESS: Added 'subject_id' column to 'adventure_worlds' table!\n";
} else {
    echo "INFO: 'subject_id' column already exists!\n";
}
