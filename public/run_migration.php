<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
echo "=== Migration Runner M-Pesa Subscriptions & Payments ===\n\n";

try {
    $migration = require database_path('migrations/2026_07_23_040000_create_subscriptions_and_payments_tables.php');
    $migration->up();
    echo "SUCCESS: Created subscriptions and payments tables!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
