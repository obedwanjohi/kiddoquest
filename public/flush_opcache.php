<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain');
echo "=== OPcache & Route Cleared ===\n";

\Illuminate\Support\Facades\Artisan::call('route:clear');
\Illuminate\Support\Facades\Artisan::call('view:clear');

echo "SUCCESS! Route count: " . count(\Illuminate\Support\Facades\Route::getRoutes()) . "\n";
foreach (\Illuminate\Support\Facades\Route::getRoutes() as $r) {
    if (str_starts_with($r->getName() ?? '', 'admin.')) {
        echo " - " . $r->getName() . " => " . $r->uri() . "\n";
    }
}
