<?php
$file = __DIR__ . '/resources/views/kids/world.blade.php';
$content = file_get_contents($file);

// Replace locked mission circle -> rounded rectangle
$content = str_replace(
    '<div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center bg-gray-200 text-3xl overflow-hidden relative">',
    '<div class="flex-shrink-0 w-28 h-16 rounded-[var(--kid-radius-md)] flex items-center justify-center bg-gray-200 text-3xl overflow-hidden relative">',
    $content
);

// Replace active mission circle -> rounded rectangle
$content = str_replace(
    '<div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-black text-white overflow-hidden relative shadow-sm"',
    '<div class="flex-shrink-0 w-28 h-16 rounded-[var(--kid-radius-md)] flex items-center justify-center text-2xl font-black text-white overflow-hidden relative shadow-sm"',
    $content
);

file_put_contents($file, $content);
echo "Updated mission thumbnails to be 16:9 rounded rectangles.\n";
