<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$divCount = substr_count($content, '<div') - substr_count($content, '</div');
$templateCount = substr_count($content, '<template') - substr_count($content, '</template');

echo "Div delta: $divCount\n";
echo "Template delta: $templateCount\n";
