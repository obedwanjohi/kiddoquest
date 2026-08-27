<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = <<<CSS
    .matching-item {
        background: white; border-radius: var(--kid-radius-md);
        border: 4px solid transparent; padding: var(--kid-space-3) var(--kid-space-4);
CSS;

$replace = <<<CSS
    .matching-item {
        flex: 1; /* Stretch all items to equal height */
        background: white; border-radius: var(--kid-radius-md);
        border: 4px solid transparent; padding: var(--kid-space-3) var(--kid-space-4);
CSS;

// Attempt to replace. Handle spaces if needed.
$content = preg_replace('/\.matching-item\s*\{\s*background:\s*white;/', ".matching-item {\n        flex: 1;\n        background: white;", $content);

file_put_contents($file, $content);
echo "Added flex: 1 to matching-item.\n";
