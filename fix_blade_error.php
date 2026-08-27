<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$content = str_replace('@error="option.image = null"', 'x-on:error="option.image = null"', $content);
$content = str_replace('@error="item.image = null"', 'x-on:error="item.image = null"', $content);
$content = str_replace('@error="chip.image = null"', 'x-on:error="chip.image = null"', $content);

file_put_contents($file, $content);
echo "Replaced @error with x-on:error to fix Blade parsing conflict.\n";
