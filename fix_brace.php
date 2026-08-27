<?php
$c = file_get_contents('resources/views/kids/mission/engine.blade.php');
$target = "    .question-prompt {
        font-family: var(--kid-font-heading);
        font-size: clamp(24px, 5vw, 32px);
        font-weight: 900; color: var(--kid-text);
        line-height: 1.3;


    .question-badge {";

$replacement = "    .question-prompt {
        font-family: var(--kid-font-heading);
        font-size: clamp(24px, 5vw, 32px);
        font-weight: 900; color: var(--kid-text);
        line-height: 1.3;
    }

    .question-badge {";
$c = str_replace($target, $replacement, $c);
file_put_contents('resources/views/kids/mission/engine.blade.php', $c);
echo "Fixed missing brace";
