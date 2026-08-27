<?php
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($bladeFile);

// Replace header
$oldHeader = '<a href="#" class="exit-btn" @click.prevent="exitQuiz()" aria-label="Exit quiz">🗺️</a>';
$newHeader = '<a href="#" class="exit-btn" @click.prevent="exitQuiz()" aria-label="Exit quiz">🏠</a>';
$content = str_replace($oldHeader, $newHeader, $content);

// Add settings icon next to star pill if it's not there
if (strpos($content, 'class="settings-btn"') === false) {
    $starPill = '<div class="star-pill" :class="{ pulse: starPulse }">';
    $settingsBtn = '<a href="#" class="settings-btn" aria-label="Settings" style="margin-left: 12px;">⚙️</a>';
    $content = preg_replace('/(<div class="star-pill" :class="\{ pulse: starPulse \}">.*?<\/div>)/s', "$1\n        $settingsBtn", $content);
}

// Ensure the speech bubble uses x-html or text correctly and remove hardcoded styles
$content = str_replace('<div class="leo-bubble" x-text="leoMessage"></div>', '<div class="leo-bubble" x-html="leoMessage.replace(/\\n/g, \'<br>\')"></div>', $content);

file_put_contents($bladeFile, $content);
echo "Header and bubble updated.\n";
