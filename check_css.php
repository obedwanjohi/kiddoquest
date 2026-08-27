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
    $depth += ($open - $close);
    if ($depth < 0) {
        echo "Negative depth at line " . ($i + 13) . " (relative): " . trim($line) . "\n";
        $depth = 0;
    }
}
echo "Final depth: " . $depth . "\n";
