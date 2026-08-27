<?php
$file = __DIR__ . '/resources/views/kids/world.blade.php';
$content = file_get_contents($file);

$content = str_replace(
    '$mission->thumbnail_image_url',
    '$mission->thumbnailMedia?->file_path',
    $content
);

file_put_contents($file, $content);
echo "Fixed world.blade.php to use thumbnailMedia->file_path\n";
