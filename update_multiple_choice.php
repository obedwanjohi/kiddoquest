<?php
$cssFile = __DIR__ . '/premium_style.css';
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';

// 1. Update CSS
$css = file_get_contents($cssFile);
$extraCss = <<<CSS

/* Dynamic Multiple Choice Layouts */

/* Square Layout */
.answer-grid.layout-square {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
}
.answer-grid.layout-square .answer-card {
    width: 110px;
    height: 110px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    font-size: 16px;
}
.answer-grid.layout-square .answer-card img {
    max-height: 50px;
    margin-bottom: 8px;
}

/* Vertical List Layout */
.answer-grid.layout-vertical {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}
.answer-grid.layout-vertical .answer-card {
    position: relative;
    padding: 16px;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.answer-grid.layout-vertical .card-index {
    position: absolute;
    top: 12px;
    left: 16px;
    font-size: 20px;
    font-weight: 900;
}
.answer-grid.layout-vertical .card-image {
    max-height: 80px;
    object-fit: contain;
}

CSS;

if (strpos($css, 'Dynamic Multiple Choice Layouts') === false) {
    file_put_contents($cssFile, $css . "\n" . $extraCss);
}

// 2. Update Blade
$content = file_get_contents($bladeFile);

// We need to replace the exact <template x-if="['multiple_choice',..."> block
// It's safer to use regex to find the block and replace it.

$newHtml = <<<HTML
                <template x-if="['multiple_choice','tap_answer','true_false','listen_choose'].includes(currentQuestion.type)">
                    <!-- Determine layout: if ANY option has a big image but short text (like 1,2,3), or if we explicitly want vertical, let's guess based on text length or image presence -->
                    <div class="answer-grid" :class="currentQuestion.options.some(o => o.image) ? 'layout-vertical' : 'layout-square'" :data-count="currentQuestion.options.length">
                        <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                            <div class="answer-card"
                                 :class="getCardClass(i, option.is_correct)"
                                 :style="\`animation-delay: \${0.1 + i * 0.08}s\`"
                                 @click="selectOption(i)"
                                 :aria-label="\`Answer: \${option.text}\`"
                                 role="button" tabindex="0"
                                 @keydown.enter="selectOption(i)"
                                 @keydown.space.prevent="selectOption(i)">
                                
                                <template x-if="currentQuestion.options.some(o => o.image)">
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <div class="card-index" x-text="i + 1" :style="'color: ' + (i%4===0 ? 'var(--btn-green-dark)' : i%4===1 ? 'var(--btn-blue-dark)' : i%4===2 ? 'var(--btn-red-dark)' : 'var(--btn-yellow-dark)')"></div>
                                        <template x-if="option.image">
                                            <img :src="option.image" :alt="option.text" x-on:error="option.image = null" class="card-image">
                                        </template>
                                        <template x-if="!option.image && option.text">
                                            <span x-text="option.text" style="font-size:32px;"></span>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!currentQuestion.options.some(o => o.image)">
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span x-text="option.text"></span>
                                    </div>
                                </template>

                                <template x-if="answered && selectedIndex === i && option.is_correct">
                                    <span class="badge" style="position:absolute; top: 10px; right: 10px; font-size: 24px;">✅</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
HTML;

// Replace from `<template x-if="['multiple_choice'` to `</template>` before Flashcard
$pattern = '/<template x-if="\[\'multiple_choice\'.*?<\/template>/s';
$content = preg_replace($pattern, $newHtml, $content, 1);

file_put_contents($bladeFile, $content);

echo "Multiple choice HTML and CSS updated.\n";

