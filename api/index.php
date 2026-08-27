<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Ensure writable directories exist in Vercel serverless /tmp environment
$storageDirs = [
    '/tmp/storage',
    '/tmp/storage/app',
    '/tmp/storage/app/public',
    '/tmp/storage/framework',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Set environment variable overrides for serverless writable paths & stability
putenv('APP_STORAGE=/tmp/storage');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('LOG_CHANNEL=stderr');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

// If no external session or cache is configured, avoid database session crashes
if (!getenv('SESSION_DRIVER')) {
    putenv('SESSION_DRIVER=cookie');
}
if (!getenv('CACHE_STORE')) {
    putenv('CACHE_STORE=array');
}

if (!getenv('APP_KEY')) {
    putenv('APP_KEY=base64:pZRR6aJq92aBh/o51jPqEovSr++Q/Neklxbfznx7Aq4=');
}

try {
    // Forward request to Laravel public entrypoint
    require __DIR__ . '/../public/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<h2>Laravel Application Error on Vercel</h2>';
    echo '<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre style="background:#f4f4f4;padding:15px;border:1px solid #ccc;overflow:auto;">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    error_log($e->getMessage() . "\n" . $e->getTraceAsString());
}

