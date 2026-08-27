<?php
$html = <<<HTML
        <div>
            {{-- LEO + QUESTION (side-by-side in landscape) --}}
            <div class="quiz-landscape-split">
                {{-- LEO ZONE --}}
                <div class="leo-zone" x-show="leoMessage">
                    <div class="leo-mascot" :class="{ celebrating: leoCelebrating }">🦁</div>
                    <div class="leo-bubble" x-text="leoMessage"></div>
                </div>

                {{-- QUESTION --}}
                <div class="question-zone" :key="'q-' + currentIndex">
                <div class="question-badge" x-text="\`\${currentQuestion.typeIcon} \${currentQuestion.typeName}\`"></div>
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
                    <img :src="currentQuestion.image" alt="Question image" class="question-image">
                </template>
                <template x-if="currentQuestion.hint && showHint">
                    <p style="color: var(--kid-text-muted); font-size: 16px; margin-top: 8px;">
                        💡 <span x-text="currentQuestion.hint"></span>
                    </p>
                </template>
                </div>
            </div>

            {{-- ANSWERS --}}
            <div class="answer-zone" :key="'a-' + currentIndex">

                {{-- Multiple Choice / Tap Answer / True False / Fill Blank --}}
                <template x-if="['multiple_choice','tap_answer','true_false','fill_blank'].includes(currentQuestion.type)">
                    <div class="answer-grid" :data-count="currentQuestion.options.length">
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
                </template>

                {{-- Flashcard --}}
                <template x-if="currentQuestion.type === 'flashcard'">
                    <div style="max-width:540px;margin:0 auto;">
                        <div class="flashcard-answer">
                            <div class="card-icon">🃏</div>
                            <div class="card-word"
                                 x-text="currentQuestion.options.find(o => o.is_correct)?.text || '—'">
                            </div>
                        </div>
                        <p style="text-align:center;color:var(--kid-text-muted);margin-bottom:16px;">
                            Did you know the answer?
                        </p>
                        <div class="flashcard-buttons">
                            <div class="answer-card"
                                 :class="answered && selectedIndex === 0 ? 'incorrect' : ''"
                                 style="background:#FEE2E2;border-color:#FCA5A5;color:#DC2626;"
                                 @click="selectFlashcard(false)">
                                ❌ No
                            </div>
                            <div class="answer-card"
                                 :class="answered && selectedIndex === 1 ? 'correct' : ''"
                                 style="background:#DCFCE7;border-color:#86EFAC;color:#16A34A;"
                                 @click="selectFlashcard(true)">
                                ✅ Yes!
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Matching (QT-03) --}}
                <template x-if="currentQuestion.type === 'matching'">
                    <div>
                        <div class="matching-progress">
                            <span class="check" x-text="matchedPairs.length"></span>
                            / <span x-text="matchLeftItems.length"></span> pairs matched!
                        </div>
                        <div class="matching-board">
                            <div class="matching-column">
                                <template x-for="(option, i) in matchLeftItems" :key="'L-' + option.id">
                                    <div class="matching-item"
                                         :class="getMatchItemClass('left', i)"
                                         @click="selectMatch('left', i)">
                                        <template x-if="option.image">
                                            <img :src="option.image" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">
                                        </template>
                                        <template x-if="option.text && option.text.trim() !== ''">
                                            <span x-text="option.text"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="matching-column">
                                <template x-for="(item, j) in matchRightItems" :key="'R-' + item.originalIndex">
                                    <div class="matching-item"
                                         :class="getMatchItemClass('right', j)"
                                         @click="selectMatch('right', j)">
                                        <template x-if="item.image">
                                            <img :src="item.image" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">
                                        </template>
                                        <template x-if="item.text && item.text.trim() !== ''">
                                            <span x-text="item.text"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Sequence / Drag & Drop Order (QT-05) --}}
                <template x-if="currentQuestion.type === 'drag_sequence'">
                    <div class="sequence-board">
                        <div class="sequence-slots-label">📋 Put them in order here:</div>
                        <div class="sequence-slots">
                            <template x-for="(slot, i) in seqSlots" :key="'slot-' + i">
                                <div class="sequence-slot"
                                     :class="getSeqSlotClass(i)"
                                     @click="selectSeqSlot(i)">
                                    <span class="slot-label" x-text="\`\${i + 1}\${i === 0 ? 'st' : i === 1 ? 'nd' : i === 2 ? 'rd' : 'th'}\`"></span>
                                    <template x-if="slot !== null">
                                        <span class="slot-number" x-text="seqCards[slot].text"></span>
                                    </template>
                                    <template x-if="slot === null">
                                        <span class="slot-placeholder">⬇️</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="sequence-tray-label">🃏 Drag or tap a number, then tap a slot:</div>
                        <div class="sequence-tray">
                            <template x-for="(card, i) in seqCards" :key="'card-' + i">
                                <div class="sequence-card"
                                     :class="getSeqCardClass(i)"
                                     @click="selectSeqCard(i)">
                                    <span x-text="card.text"></span>
                                </div>
                            </template>
                        </div>
                        <button class="sequence-check-btn"
                                :disabled="seqSlots.includes(null)"
                                @click="checkSequence()">
                            ✅ Check My Answer!
                        </button>
                    </div>
                </template>

                {{-- Count Objects (QT-09) --}}
                <template x-if="currentQuestion.type === 'count_objects'">
                    <div>
                        <div class="count-objects-display" x-show="(currentQuestion.metadata?.objects || []).length > 0">
                            <div class="count-objects-emoji">
                                <template x-for="(emoji, i) in (currentQuestion.metadata?.objects || [])" :key="i">
                                    <span class="count-emoji" x-text="emoji"></span>
                                </template>
                            </div>
                        </div>
                        <div class="answer-grid" :data-count="currentQuestion.options.length">
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

// I will find the EXACT spot where the corruption starts:
//     {{-- MAIN QUIZ AREA (hidden when complete) --}}
//     <template x-if="!quizComplete">
//         <template x-if="option.image">
// Wait! Let's just find `    <template x-if="!quizComplete">` and slice the file there.
$lines = explode("\n", $content);
$newLines = [];
$skip = false;
$inserted = false;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], '    <template x-if="!quizComplete">') !== false) {
        $newLines[] = $lines[$i];
        $newLines[] = $html;
        $skip = true; // skip everything until Memory Match
        continue;
    }
    
    if ($skip && strpos($lines[$i], '{{-- Memory Match (QT-11) --}}') !== false) {
        $skip = false;
    }

    if (!$skip) {
        $newLines[] = $lines[$i];
    }
}

file_put_contents($file, implode("\n", $newLines));
echo "Inserted recovered block successfully.\n";
