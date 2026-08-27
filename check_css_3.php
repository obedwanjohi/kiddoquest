<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

preg_match('/<style>(.*?)<\/style>/s', $content, $matches);
$css = $matches[1];

$lines = explode("\n", $css);
$depth = 0;
foreach ($lines as $i => $line) {
    $open = substr_count($line, '{');
    $close = substr_count($line, '}');
    
    if ($open > 0 || $close > 0) {
        $depth += ($open - $close);
        if ($depth > 0 && !str_contains($line, '@keyframes') && !str_contains($line, '@media')) {
            // It's normal to be 1 inside a class. But if it ends the line at 1, let's see if the NEXT line has {
        }
    }
    
    // Check if a block wasn't closed before a new one started
    if ($depth == 1 && $open > 0 && $close == 0 && trim($line) !== '') {
        // Just opened a block.
    }
    
    // We want to find where it FAILS to go back to 0 before opening a NEW block.
    // If a line starts with .class or #id or /* and depth is > 0
    if ($depth > 0 && (str_starts_with(trim($line), '.') || str_starts_with(trim($line), '/*'))) {
        // This might be a missing close brace!
        echo "Line " . ($i + 1) . " (relative): depth=$depth -> " . trim($line) . "\n";
    }
}
