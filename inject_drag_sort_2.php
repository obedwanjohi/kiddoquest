<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$target = '{{-- Fallback (same as multiple choice) --}}';

$injection = <<<HTML
                {{-- Drag & Sort (QT-04) --}}
                <template x-if="currentQuestion.type === 'drag_sort'">
                    <div class="sort-board">
                        {{-- Item tray (unsorted chips) --}}
                        <div class="sequence-tray-label">👇 Tap an item, then tap a box!</div>
                        <div class="sort-tray">
                            <template x-for="(chip, i) in sortChips" :key="'chip-' + i">
                                <div class="sort-chip"
                                     :class="getSortChipClass(i)"
                                     @click="selectSortChip(i)"
                                     x-show="chip.bucket === null"
                                     style="display:flex; justify-content:center; align-items:center;">
                                    <template x-if="chip.image">
                                        <img :src="chip.image" alt="" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">
                                    </template>
                                    <template x-if="!chip.image && chip.text && chip.text.trim() !== ''">
                                        <span x-text="chip.text"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Category buckets --}}
                        <div class="sort-buckets">
                            <template x-for="(cat, bi) in sortCategories" :key="'bucket-' + bi">
                                <div class="sort-bucket"
                                     :class="getSortBucketClass(bi)"
                                     @click="selectSortBucket(bi)">
                                    <div class="sort-bucket-label" x-text="cat"></div>
                                    <div class="sort-bucket-items">
                                        <template x-for="(chip, ci) in sortChips" :key="'bucket-chip-' + ci">
                                            <div class="sort-chip in-bucket"
                                                 :class="getSortChipInBucketClass(ci)"
                                                 x-show="chip.bucket === cat"
                                                 style="display:flex; justify-content:center; align-items:center;">
                                                <template x-if="chip.image">
                                                    <img :src="chip.image" alt="" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">
                                                </template>
                                                <template x-if="!chip.image && chip.text && chip.text.trim() !== ''">
                                                    <span x-text="chip.text"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                
HTML;

if (strpos($content, "<template x-if=\"currentQuestion.type === 'drag_sort'\">") === false) {
    $content = str_replace($target, $injection . $target, $content);
    file_put_contents($file, $content);
    echo "SUCCESS: Injected drag_sort UI.\n";
} else {
    echo "ALREADY EXISTS or error.\n";
}
