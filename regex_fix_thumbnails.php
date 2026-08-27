<?php
$file = __DIR__ . '/resources/views/kids/world.blade.php';
$content = file_get_contents($file);

// Fix Active Mission
$pattern = '/<div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-black text-white"([^>]+)>([^<]*)@if\(\$isCompleted\)(.*?)@elseif\(\$isInProgress\)(.*?)@else(.*?)@endif([^<]*)<\/div>/s';
$replacement = <<<HTML
<div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-black text-white overflow-hidden relative shadow-sm"$1>
    @if(\$mission->thumbnailMedia?->file_path)
        <img src="{{ \$mission->thumbnailMedia->file_path }}" class="w-full h-full object-cover absolute inset-0 z-0" alt="">
        <div class="absolute inset-0 bg-black/20 z-10"></div>
    @endif
    <div class="relative z-20">$2@if(\$isCompleted)$3@elseif(\$isInProgress)$4@else$5@endif$6</div>
</div>
HTML;

$content = preg_replace($pattern, $replacement, $content);

file_put_contents($file, $content);
echo "Used preg_replace to fix active mission circle.\n";
