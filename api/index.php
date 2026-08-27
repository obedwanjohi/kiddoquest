<?php

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

// Set environment variable overrides for serverless writable paths
putenv('APP_STORAGE=/tmp/storage');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
if (!getenv('APP_KEY')) {
    putenv('APP_KEY=base64:pZRR6aJq92aBh/o51jPqEovSr++Q/Neklxbfznx7Aq4=');
}

// Forward request to Laravel public entrypoint
require __DIR__ . '/../public/index.php';
