<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$lines = explode("\n", $content);
$newLines = [];
$skip = false;

for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], '{{-- The missing slot') !== false && strpos($lines[$i], 'until answered') !== false) {
        // We found the missing slot comment.
        // We need to inject the missing wrapper before this line, but AFTER the previous template.
        // Let's just hardcode the injection since we know the context.
    }
}

// Let's just use preg_replace to be safe with the em-dash.
$pattern = '/\s*<\/template>\s*<\/template>\s*\{\{-- The missing slot [^\n]*until answered/';

$replacement = <<<EOT

                        </div>
                    </div>
                </template>

                {{-- Complete the Pattern (QT-10) --}}
                <template x-if="currentQuestion.type === 'complete_pattern'">
                    <div class="pattern-board">
                        {{-- Pattern strip: shows sequence tiles + missing "?" slot --}}
                        <div class="pattern-strip" x-show="(currentQuestion.metadata?.sequence || []).length > 0">
                            <template x-for="(item, i) in (currentQuestion.metadata?.sequence || [])" :key="'seq-' + i">
                                <div class="pattern-tile"
                                     :style="`animation-delay: \${0.1 + i * 0.1}s`">
                                    <span x-text="item"></span>
                                </div>
                            </template>
                            {{-- The missing slot — shows "?" until answered, then reveals correct answer --}}
EOT;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Fixed engine.blade.php properly using preg_replace\n";
