<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

// We replace the text condition so it only shows text IF there is NO image.
$search = '<template x-if="option.text && option.text.trim() !== \'\'">';
$replace = '<template x-if="!option.image && option.text && option.text.trim() !== \'\'">';

$content = str_replace($search, $replace, $content);

file_put_contents($file, $content);
echo "Updated option text rendering logic.\n";
