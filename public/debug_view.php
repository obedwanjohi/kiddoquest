<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/kids/profiles', 'GET');
$response = $kernel->handle($request);

header('Content-Type: text/plain');
echo "=== HTTP Kernel Debug ===\n";
echo "Status Code: " . $response->getStatusCode() . "\n\n";

if ($response->getStatusCode() === 500) {
    if (isset($response->exception)) {
        $e = $response->exception;
        echo "EXCEPTION CLASS: " . get_class($e) . "\n";
        echo "EXCEPTION MESSAGE: " . $e->getMessage() . "\n";
        echo "FILE: " . $e->getFile() . " LINE: " . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
    } else {
        echo "CONTENT: " . substr($response->getContent(), 0, 1000);
    }
} else {
    echo "SUCCESS HTTP KERNEL! Content length: " . strlen($response->getContent());
}
