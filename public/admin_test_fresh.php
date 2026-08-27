<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::create('/dev/admin', 'GET')
);

header('Content-Type: text/plain');
echo "=== Dev Admin Route Test ===\n";
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Location Header: " . $response->headers->get('Location') . "\n";
