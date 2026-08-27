<?php
$file = __DIR__ . '/resources/views/kids/world.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    '$mission->thumbnailMedia?->file_path',
    '$mission->thumbnailMedia?->url',
    $content
);
$content = str_replace(
    '$mission->thumbnailMedia->file_path',
    '$mission->thumbnailMedia->url',
    $content
);

file_put_contents($file, $content);
echo "Fixed thumbnail URL generation in world.blade.php\n";
