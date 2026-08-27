<?php
$lines = file('diff_dump.txt');
$extracted = [];
$recording = false;

foreach ($lines as $line) {
    if (strpos($line, '+<div>') === 0) {
        $recording = true;
    }
    if ($recording) {
        if (strpos($line, '+') === 0) {
            $extracted[] = substr($line, 1);
        } else if (strpos($line, '@@') === 0) {
            // End of block
            break;
        }
    }
}

file_put_contents('extracted_code.php', implode("", $extracted));
echo "Extracted " . count($extracted) . " lines of code.\n";
