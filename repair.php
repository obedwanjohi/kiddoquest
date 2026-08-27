<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$lines = explode("\n", $content);
$newLines = [];
$skip = false;

for ($i = 0; $i < count($lines); $i++) {
    // If we hit the beginning of the bad duplication at line 1162
    if (!$skip && strpos($lines[$i], '<div>') !== false && 
        isset($lines[$i+1]) && strpos($lines[$i+1], '{{-- LEO + QUESTION (side-by-side in landscape) --}}') !== false &&
        isset($lines[$i+2]) && strpos($lines[$i+2], '<div class="quiz-landscape-split">') !== false) {
        $skip = true;
    }

    // End of the bad duplication is right before the NEXT {{-- Drag & Sort (QT-04) --}}
    if ($skip && strpos($lines[$i], '{{-- Drag & Sort (QT-04) --}}') !== false) {
        $skip = false;
        
        // Wait, the block I need to inject to close the prior tag is:
        $newLines[] = "                                    </template>";
        $newLines[] = "                                    <template x-if=\"answered && selectedIndex === i && option.is_correct\">";
        $newLines[] = "                                        <span class=\"badge\">✅</span>";
        $newLines[] = "                                    </template>";
        $newLines[] = "                                </div>";
        $newLines[] = "                            </template>";
        $newLines[] = "                        </div>";
        $newLines[] = "                    </div>";
        $newLines[] = "                </template>";
        $newLines[] = "";
        $newLines[] = "                {{-- Complete the Pattern (QT-10) --}}";
        $newLines[] = "                <template x-if=\"currentQuestion.type === 'complete_pattern'\">";
        $newLines[] = "                    <div class=\"pattern-board\">";
        $newLines[] = "                        {{-- Pattern strip: shows sequence tiles + missing \"?\" slot --}}";
        $newLines[] = "                        <div class=\"pattern-strip\" x-show=\"(currentQuestion.metadata?.sequence || []).length > 0\">";
        $newLines[] = "                            <template x-for=\"(item, i) in (currentQuestion.metadata?.sequence || [])\" :key=\"'seq-' + i\">";
        $newLines[] = "                                <div class=\"pattern-tile\"";
        $newLines[] = "                                     :style=\"`animation-delay: \${0.1 + i * 0.1}s`\">";
        $newLines[] = "                                    <span x-text=\"item\"></span>";
        $newLines[] = "                                </div>";
        $newLines[] = "                            </template>";
        $newLines[] = "                            {{-- The missing slot — shows \"?\" until answered, then reveals correct answer --}}";
        $newLines[] = "                            <div class=\"pattern-tile missing\"";
        $newLines[] = "                                 :class=\"answered ? 'revealed' : ''\"";
        $newLines[] = "                                 :style=\"`animation-delay: \${0.1 + (currentQuestion.metadata?.sequence?.length || 0) * 0.1}s`\">";
        $newLines[] = "                                <template x-if=\"!answered\">";
        $newLines[] = "                                    <span class=\"question-mark\">?</span>";
        $newLines[] = "                                </template>";
        $newLines[] = "                                <template x-if=\"answered\">";
        $newLines[] = "                                    <span x-text=\"getCorrectAnswerText()\"></span>";
        $newLines[] = "                                </template>";
        $newLines[] = "                            </div>";
        $newLines[] = "                        </div>";
        $newLines[] = "";
        $newLines[] = "                        {{-- Arrow pointing down to answers --}}";
        $newLines[] = "                        <div style=\"text-align:center;margin-bottom:var(--kid-space-3);\">";
        $newLines[] = "                            <span class=\"pattern-arrow\">⬇️</span>";
        $newLines[] = "                        </div>";
        $newLines[] = "";
        $newLines[] = "                        {{-- Answer choices --}}";
        $newLines[] = "                        <div class=\"pattern-answers-label\">Tap your answer!</div>";
        $newLines[] = "                        <div class=\"pattern-answer-grid\">";
        $newLines[] = "                            <template x-for=\"(option, i) in currentQuestion.options\" :key=\"option.id\">";
        $newLines[] = "                                <div class=\"answer-card\"";
        $newLines[] = "                                     :class=\"getCardClass(i, option.is_correct)\"";
        $newLines[] = "                                     :style=\"`animation-delay: \${0.1 + i * 0.08}s`\"";
        $newLines[] = "                                     @click=\"selectOption(i)\"";
        $newLines[] = "                                     :aria-label=\"`Answer: \${option.text}`\"";
        $newLines[] = "                                     role=\"button\" tabindex=\"0\"";
        $newLines[] = "                                     @keydown.enter=\"selectOption(i)\"";
        $newLines[] = "                                     @keydown.space.prevent=\"selectOption(i)\">";
        $newLines[] = "                                    <template x-if=\"option.image\">";
        $newLines[] = "                                        <img :src=\"option.image\" :alt=\"option.text\" class=\"card-image\" style=\"max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;\">";
        $newLines[] = "                                    </template>";
        $newLines[] = "                                    <template x-if=\"option.text && option.text.trim() !== ''\">";
        $newLines[] = "                                        <span x-text=\"option.text\"></span>";
        $newLines[] = "                                    </template>";
        $newLines[] = "                                    <template x-if=\"answered && selectedIndex === i && option.is_correct\">";
        $newLines[] = "                                        <span class=\"badge\">✅</span>";
        $newLines[] = "                                    </template>";
        $newLines[] = "                                </div>";
        $newLines[] = "                            </template>";
        $newLines[] = "                        </div>";
        $newLines[] = "                    </div>";
        $newLines[] = "                </template>";
        $newLines[] = "";
    }

    if (!$skip) {
        $newLines[] = $lines[$i];
    }
}

file_put_contents($file, implode("\n", $newLines));
echo "Repaired engine.blade.php\n";
