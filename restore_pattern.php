<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = <<<EOT
                        </div>
                    </div>
                </template>
                            </template>
                            {{-- The missing slot ?" shows "?" until answered, then reveals correct answer --}}
EOT;

$replace = <<<EOT
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
                            {{-- The missing slot ?" shows "?" until answered, then reveals correct answer --}}
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Fixed engine.blade.php pattern rendering issue\n";
