<?php
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($bladeFile);

// Create directories
@mkdir(__DIR__ . '/resources/views/kids/mission/types', 0777, true);
@mkdir(__DIR__ . '/public/css/kids/mission/types', 0777, true);
@mkdir(__DIR__ . '/public/js/kid', 0777, true);

// 1. Extract CSS
preg_match('/<style>\s*\/\* NEW PREMIUM STYLES \*\/(.*?)<\/style>/s', $content, $cssMatch);
if ($cssMatch) {
    $fullCss = $cssMatch[1];
    
    // Split CSS manually for safety. Actually, let's just dump it all into core.css for now, 
    // because extracting specific blocks requires a real CSS parser. 
    // Wait, it's safer to just move the entire block to public/css/kids/mission/core.css
    file_put_contents(__DIR__ . '/public/css/kids/mission/core.css', "/* NEW PREMIUM STYLES */\n" . $fullCss);
    
    // Replace <style> block with <link>
    $linkTag = '<link rel="stylesheet" href="{{ asset(\'css/kids/mission/core.css\') }}?v=' . time() . '">';
    $content = preg_replace('/<style>\s*\/\* NEW PREMIUM STYLES \*\/.*?<\/style>/s', $linkTag, $content);
}

// 2. Extract HTML Template for Multiple Choice
preg_match('/(<!-- MULTIPLE CHOICE LAYOUTS -->.*?<!-- OTHER QUESTION TYPES WILL GO HERE LATER -->)/s', $content, $htmlMatch);
if ($htmlMatch) {
    $multipleChoiceHtml = $htmlMatch[1];
    // Write to its own blade file
    file_put_contents(__DIR__ . '/resources/views/kids/mission/types/multiple_choice.blade.php', $multipleChoiceHtml);
    
    // Replace in engine with @include
    $includeTag = "@include('kids.mission.types.multiple_choice')";
    $content = str_replace($multipleChoiceHtml, $includeTag . "\n                <!-- OTHER QUESTION TYPES WILL GO HERE LATER -->", $content);
}

// 3. Extract Alpine JS quizEngine
preg_match('/<script>\s*function quizEngine\(config\)(.*?)<\/script>/s', $content, $jsMatch);
if ($jsMatch) {
    $jsCode = "function quizEngine(config)" . $jsMatch[1];
    file_put_contents(__DIR__ . '/public/js/kid/quiz-engine.js', $jsCode);
    
    // Replace script tag with external src
    $scriptTag = '<script src="{{ asset(\'js/kid/quiz-engine.js\') }}?v=' . time() . '"></script>';
    $content = preg_replace('/<script>\s*function quizEngine\(config\).*?<\/script>/s', $scriptTag, $content);
}

file_put_contents($bladeFile, $content);
echo "Refactoring complete.\n";
