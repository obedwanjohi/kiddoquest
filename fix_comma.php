<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$content = str_replace("},,", "},", $content);
$content = str_replace("},\n,", "},", $content);
$content = str_replace("},\r\n,", "},", $content);

file_put_contents($file, $content);
echo "Fixed double comma syntax error.\n";
