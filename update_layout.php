<?php

$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

// 1. Swap prompt layout
$search1 = <<<EOT
                <template x-if="currentQuestion.image">
                    <img :src="currentQuestion.image" alt="Question image" class="question-image">
                </template>
                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap;">
                    <button @click="playQuestionAudio()" 
                            class="kid-btn" 
                            style="width: 48px; height: 48px; border-radius: 50%; background: var(--kid-primary); color: white; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 4px 0 rgba(0,0,0,0.15);"
                            aria-label="Replay Audio">
                        🔊
                    </button>
                    <div class="question-prompt" x-text="currentQuestion.prompt" style="margin: 0;"></div>
                </div>
EOT;

$replace1 = <<<EOT
                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <button @click="playQuestionAudio()" 
                            class="kid-btn" 
                            style="width: 48px; height: 48px; border-radius: 50%; background: var(--kid-primary); color: white; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 4px 0 rgba(0,0,0,0.15);"
                            aria-label="Replay Audio">
                        🔊
                    </button>
                    <div class="question-prompt" x-text="currentQuestion.prompt" style="margin: 0;"></div>
                </div>
                <template x-if="currentQuestion.image">
                    <img :src="currentQuestion.image" alt="Question image" class="question-image" style="margin-top: 16px;">
                </template>
EOT;

$content = str_replace($search1, $replace1, $content);

// 2. Replace the span options with img + span fallback
$searchSpan = '<span x-text="option.text"></span>';
$replaceSpan = <<<EOT
<template x-if="option.image">
                                        <img :src="option.image" :alt="option.text" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">
                                    </template>
                                    <template x-if="option.text && option.text.trim() !== ''">
                                        <span x-text="option.text"></span>
                                    </template>
EOT;

// I'll manually replace the specific ones that lack <img :src="option.image"> above them.
// A simpler way: we just replace ALL occurrences of `<span x-text="option.text"></span>` 
// that are preceded by `<template x-if="option.image">` ... wait, if they ALREADY have it, we don't want duplicates.
// The ones we want to replace are exactly lines 1157, 1208, 1296, 1432.
// We can just explode by line and replace them directly.
$lines = explode("\n", $content);
$targetLines = [1157, 1208, 1296, 1432];

foreach ($targetLines as $ln) {
    $idx = $ln - 1; // 0-indexed
    if (strpos($lines[$idx], 'span x-text="option.text"') !== false) {
        $padding = str_repeat(' ', strspn($lines[$idx], ' '));
        $lines[$idx] = $padding . "<template x-if=\"option.image\">\n"
                     . $padding . "    <img :src=\"option.image\" :alt=\"option.text\" class=\"card-image\" style=\"max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;\">\n"
                     . $padding . "</template>\n"
                     . $padding . "<template x-if=\"option.text && option.text.trim() !== ''\">\n"
                     . $padding . "    <span x-text=\"option.text\"></span>\n"
                     . $padding . "</template>";
    }
}

$content = implode("\n", $lines);
file_put_contents($file, $content);

echo "Updated engine.blade.php\n";
