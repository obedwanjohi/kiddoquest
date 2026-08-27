<?php
$transcript = 'C:/Users/livewave/.gemini/antigravity/brain/f4af740d-d30f-425a-94e4-182f02b06858/.system_generated/logs/transcript.jsonl';
$lines = file($transcript);

$diffBlock = [];
$inDiff = false;

foreach ($lines as $line) {
    $data = json_decode($line, true);
    if ($data && isset($data['content'])) {
        $content = $data['content'];
        if (strpos($content, '[diff_block_start]') !== false) {
            $diffBlock = [];
            $parts = explode("\n", $content);
            $recording = false;
            foreach ($parts as $p) {
                if (strpos($p, '[diff_block_start]') !== false) {
                    $recording = true;
                    continue;
                }
                if (strpos($p, '[diff_block_end]') !== false) {
                    $recording = false;
                }
                if ($recording) {
                    $diffBlock[] = $p;
                }
            }
        }
    }
}

file_put_contents('diff_dump.txt', implode("\n", $diffBlock));
echo "Dumped diff block length: " . count($diffBlock) . "\n";
