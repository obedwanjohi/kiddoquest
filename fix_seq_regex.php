<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = '/text:\s*o\.text,\s*correctIndex:\s*i,/';
$replace = "text: o.text, image: o.image, correctIndex: i,";

$newContent = preg_replace($search, $replace, $content);

if ($newContent !== $content && $newContent !== null) {
    file_put_contents($file, $newContent);
    echo "SUCCESS: Added image to seqCards initialization.\n";
} else {
    echo "FAILED: Could not find target pattern in engine.blade.php.\n";
}
