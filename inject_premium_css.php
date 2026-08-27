<?php
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$cssFile = __DIR__ . '/premium_style.css';

$content = file_get_contents($bladeFile);
$css = file_get_contents($cssFile);

// Replace the entire <style> block
$content = preg_replace('/<style>.*?<\/style>/s', "<style>\n" . $css . "\n</style>", $content);

file_put_contents($bladeFile, $content);
echo "Premium CSS injected successfully.\n";
