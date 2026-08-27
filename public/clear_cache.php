<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
}

$dir = __DIR__ . '/../storage/framework/views';
$files = glob($dir . '/*.php');
$count = 0;
foreach ($files as $f) {
    if (is_file($f)) {
        unlink($f);
        $count++;
    }
}

header('Content-Type: text/plain');
echo "=== View Cache Hard Wiped ===\n";
echo "Deleted {$count} compiled blade view files from storage/framework/views!\n";
