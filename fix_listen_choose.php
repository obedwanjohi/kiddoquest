<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$search = "['multiple_choice','tap_answer','true_false','fill_blank']";
$replace = "['multiple_choice','tap_answer','true_false','fill_blank','listen_choose']";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added listen_choose to the multiple choice grid rendering condition.\n";
