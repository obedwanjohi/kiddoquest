<?php
$c = file_get_contents('resources/views/kids/mission/engine.blade.php');

$firstExtends = strpos($c, "@extends('kids.layouts.app')");
$secondExtends = strpos($c, "@extends('kids.layouts.app')", $firstExtends + 1);

if ($secondExtends !== false) {
    $endStr = "    .question-badge {";
    $endPos = strpos($c, $endStr, $secondExtends);
    
    if ($endPos !== false) {
        $chunkToRemove = substr($c, $secondExtends, $endPos - $secondExtends);
        $replacement = "    .question-zone {\n        text-align: center; padding: var(--kid-space-4) var(--kid-space-5);\n    }\n    .question-prompt {\n        font-family: var(--kid-font-heading);\n        font-size: clamp(24px, 5vw, 32px);\n        font-weight: 900; color: var(--kid-text);\n        line-height: 1.3;\n    }\n\n";
        $c = str_replace($chunkToRemove, $replacement, $c);
        file_put_contents('resources/views/kids/mission/engine.blade.php', $c);
        echo "Fixed duplication!\n";
    } else {
        echo "Could not find endStr";
    }
} else {
    echo "No duplication found";
}
