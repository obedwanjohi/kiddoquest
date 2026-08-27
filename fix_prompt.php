<?php
$bladeFile = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($bladeFile);

// Replace the line that incorrectly hides the prompt when leoMessage exists
$oldCode = "<span x-html=\"leoMessage ? leoMessage.replace(/\\\\n/g, '<br>') : currentQuestion.prompt\"></span>";
$newCode = "<span x-html=\"currentQuestion.prompt\"></span>";

$content = str_replace($oldCode, $newCode, $content);

file_put_contents($bladeFile, $content);
echo "Prompt visibility fixed.\n";
