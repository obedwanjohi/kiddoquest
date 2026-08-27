<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}

$dir = __DIR__ . '/../storage/framework/views';
foreach (glob($dir . '/*.php') as $f) {
    if (is_file($f)) @unlink($f);
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$admin = \App\Models\Admin::first() ?? new \App\Models\Admin(['name' => 'Super Admin', 'email' => 'admin@example.com', 'role' => 'admin']);
\Illuminate\Support\Facades\Auth::guard('admin')->login($admin);

$request = Illuminate\Http\Request::create('/admin/dashboard', 'GET');
$response = $kernel->handle($request);

header('Content-Type: text/plain');
echo "=== Test Admin Dashboard Script ===\n";
echo "Status Code: " . $response->getStatusCode() . "\n\n";

if ($response->getStatusCode() === 500) {
    if (isset($response->exception)) {
        $e = $response->exception;
        echo "EXCEPTION CLASS: " . get_class($e) . "\n";
        echo "EXCEPTION MESSAGE: " . $e->getMessage() . "\n";
        echo "FILE: " . $e->getFile() . " LINE: " . $e->getLine() . "\n\n";
        echo $e->getTraceAsString();
    }
} else {
    echo "SUCCESS RENDERING ADMIN DASHBOARD! Length: " . strlen($response->getContent());
}
