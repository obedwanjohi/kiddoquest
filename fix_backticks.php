<?php
$c = file_get_contents('resources/views/kids/mission/engine.blade.php');

$old = '\`';
$new = '`';
$c = str_replace($old, $new, $c);

file_put_contents('resources/views/kids/mission/engine.blade.php', $c);
echo "Fixed backticks!";
