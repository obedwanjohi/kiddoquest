<?php
$transcript = 'C:/Users/livewave/.gemini/antigravity/brain/f4af740d-d30f-425a-94e4-182f02b06858/.system_generated/logs/transcript.jsonl';
$lines = file($transcript);

$views = [];
foreach ($lines as $line) {
    $data = json_decode($line, true);
    if ($data && $data['type'] === 'VIEW_FILE') {
        $content = $data['content'] ?? '';
        if (strpos($content, 'engine.blade.php') !== false) {
            $views[] = strlen($content);
            file_put_contents("view_" . count($views) . ".txt", $content);
        }
    }
}
echo "Found " . count($views) . " view_file calls. Lengths: " . implode(", ", $views) . "\n";
