<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

// 1. Fix matchRightItems text condition
$content = str_replace(
    '<template x-if="item.text && item.text.trim() !== \'\'">',
    '<template x-if="!item.image && item.text && item.text.trim() !== \'\'">',
    $content
);

// 2. Add @error to all images to trigger fallback
// matchLeftItems
$content = str_replace(
    '<img :src="option.image" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">',
    '<img :src="option.image" @error="option.image = null" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">',
    $content
);

// matchRightItems
$content = str_replace(
    '<img :src="item.image" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">',
    '<img :src="item.image" @error="item.image = null" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">',
    $content
);

// answer-grid
$content = str_replace(
    '<img :src="option.image" :alt="option.text" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">',
    '<img :src="option.image" :alt="option.text" @error="option.image = null" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">',
    $content
);

// drag_sort chips
$content = str_replace(
    '<img :src="chip.image" alt="" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">',
    '<img :src="chip.image" alt="" @error="chip.image = null" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">',
    $content
);

$content = str_replace(
    '<img :src="chip.image" alt="" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">',
    '<img :src="chip.image" alt="" @error="chip.image = null" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">',
    $content
);

file_put_contents($file, $content);
echo "Applied image fallbacks and fixed matchRightItems text rendering.\n";
