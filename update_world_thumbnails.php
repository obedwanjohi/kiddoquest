<?php
$file = __DIR__ . '/resources/views/kids/world.blade.php';
$content = file_get_contents($file);

// Replace Locked Mission circle
$lockedSearch = <<<HTML
                            <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center bg-gray-200 text-3xl">
                                🔒
                            </div>
HTML;

$lockedReplace = <<<HTML
                            <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center bg-gray-200 text-3xl overflow-hidden relative">
                                @if(\$mission->thumbnail_image_url)
                                    <img src="{{ \$mission->thumbnail_image_url }}" class="w-full h-full object-cover grayscale opacity-50" alt="">
                                    <div class="absolute inset-0 flex items-center justify-center z-10">🔒</div>
                                @else
                                    🔒
                                @endif
                            </div>
HTML;

$content = str_replace($lockedSearch, $lockedReplace, $content);

// Replace Active Mission circle
$activeSearch = <<<HTML
                                <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-black text-white"
                                     style="background: {{ \$isCompleted ? '#22C55E' : (\$isInProgress ? '#F59E0B' : \$world->theme_color) }};">
                                    @if(\$isCompleted)
                                        ✔
                                    @elseif(\$isInProgress)
                                        ▶ 
                                    @else
                                        {{ \$loop->iteration }}
                                    @endif
                                </div>
HTML;

$activeReplace = <<<HTML
                                <div class="flex-shrink-0 w-16 h-16 rounded-full flex items-center justify-center text-2xl font-black text-white overflow-hidden relative shadow-sm"
                                     style="background: {{ \$isCompleted ? '#22C55E' : (\$isInProgress ? '#F59E0B' : \$world->theme_color) }};">
                                    @if(\$mission->thumbnail_image_url)
                                        <img src="{{ \$mission->thumbnail_image_url }}" class="w-full h-full object-cover absolute inset-0 z-0" alt="">
                                        {{-- Optional overlay to make text/icons readable if you want them on top of the image --}}
                                        <div class="absolute inset-0 bg-black/20 z-10"></div>
                                    @endif
                                    
                                    <div class="relative z-20">
                                        @if(\$isCompleted)
                                            ✔
                                        @elseif(\$isInProgress)
                                            ▶ 
                                        @else
                                            {{ \$loop->iteration }}
                                        @endif
                                    </div>
                                </div>
HTML;

$content = str_replace($activeSearch, $activeReplace, $content);

file_put_contents($file, $content);
echo "Updated world.blade.php\n";
