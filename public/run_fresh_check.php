<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$req = \Illuminate\Http\Request::create('/dev/admin', 'GET');
$res = $kernel->handle($req);

header('Content-Type: text/plain');
echo "=== Fresh Check ===\n";
echo "Status Code: " . $res->getStatusCode() . "\n";
echo "Redirect URL: " . $res->headers->get('Location') . "\n";
