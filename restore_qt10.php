<?php
$html = <<<HTML
                {{-- Complete the Pattern (QT-10) --}}
                <template x-if="currentQuestion.type === 'complete_pattern'">
                    <div class="pattern-board">
                        {{-- Pattern strip: shows sequence tiles + missing "?" slot --}}
                        <div class="pattern-strip" x-show="(currentQuestion.metadata?.sequence || []).length > 0">
                            <template x-for="(item, i) in (currentQuestion.metadata?.sequence || [])" :key="'seq-' + i">
                                <div class="pattern-tile"
                                     :style="\`animation-delay: \${0.1 + i * 0.1}s\`">
                                    <span x-text="item"></span>
                                </div>
                            </template>
                            {{-- The missing slot — shows "?" until answered, then reveals correct answer --}}
                            <div class="pattern-tile missing"
                                 :class="answered ? 'revealed' : ''"
                                 :style="\`animation-delay: \${0.1 + (currentQuestion.metadata?.sequence?.length || 0) * 0.1}s\`">
                                <template x-if="!answered">
                                    <span class="question-mark">?</span>
                                </template>
                                <template x-if="answered">
                                    <span x-text="getCorrectAnswerText()"></span>
                                </template>
                            </div>
                        </div>

                        {{-- Arrow pointing down to answers --}}
                        <div style="text-align:center;margin-bottom:var(--kid-space-3);">
                            <span class="pattern-arrow" x-show="(currentQuestion.metadata?.sequence || []).length > 0">⬇️</span>
                        </div>

                        {{-- Answer choices --}}
                        <div class="pattern-answers-label">Tap your answer!</div>
                        <div class="pattern-answer-grid">
                            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                <div class="answer-card"
                                     :class="getCardClass(i, option.is_correct)"
                                     :style="\`animation-delay: \${0.1 + i * 0.08}s\`"
                                     @click="selectOption(i)"
                                     :aria-label="\`Answer: \${option.text}\`"
                                     role="button" tabindex="0"
                                     @keydown.enter="selectOption(i)"
                                     @keydown.space.prevent="selectOption(i)">
                                    <template x-if="option.image">
                                        <img :src="option.image" :alt="option.text" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">
                                    </template>
                                    <template x-if="option.text && option.text.trim() !== ''">
                                        <span x-text="option.text"></span>
                                    </template>
                                    <template x-if="answered && selectedIndex === i && option.is_correct">
                                        <span class="badge">✅</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
HTML;

$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$lines = explode("\n", $content);
$newLines = [];

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], '{{-- Memory Match (QT-11) --}}') !== false) {
        $newLines[] = $html;
        $newLines[] = "";
    }
    $newLines[] = $lines[$i];
}

file_put_contents($file, implode("\n", $newLines));
echo "Restored QT-10 before QT-11.\n";
