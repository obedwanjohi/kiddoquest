<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$lines = explode("\n", $content);
$divCount = 0;
$templateCount = 0;

for ($i = 0; $i < count($lines); $i++) {
    $divCount += substr_count($lines[$i], '<div') - substr_count($lines[$i], '</div');
    $templateCount += substr_count($lines[$i], '<template') - substr_count($lines[$i], '</template');
    
    if ($divCount < 0) {
        echo "Div count goes negative at line " . ($i + 1) . "\n";
        echo $lines[$i] . "\n";
        $divCount = 0; // reset to keep finding
    }
    if ($templateCount < 0) {
        echo "Template count goes negative at line " . ($i + 1) . "\n";
        echo $lines[$i] . "\n";
        $templateCount = 0; // reset
    }
}
echo "Final Div delta: $divCount\n";
echo "Final Template delta: $templateCount\n";
