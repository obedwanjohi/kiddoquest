<?php
$logPath = 'C:\Users\livewave\.gemini\antigravity\brain\f4af740d-d30f-425a-94e4-182f02b06858\.system_generated\logs\transcript.jsonl';
$lines = file($logPath);

$targetContent = null;
foreach ($lines as $line) {
    if (strpos($line, 'engine.blade.php') !== false && strpos($line, '@extends') !== false && strlen($line) > 50000) {
        $data = json_decode($line, true);
        
        // Try to find the exact code block
        if (isset($data['tool_calls'])) {
            foreach ($data['tool_calls'] as $call) {
                if ($call['name'] === 'write_to_file' && isset($call['args']['CodeContent'])) {
                    if (strpos($call['args']['CodeContent'], 'engine.blade.php') !== false || strpos($call['args']['CodeContent'], '@extends') !== false) {
                        $targetContent = $call['args']['CodeContent'];
                        break 2;
                    }
                }
            }
        }
    }
}

if ($targetContent) {
    file_put_contents('recovered_engine.php', $targetContent);
    echo "Recovered perfectly to recovered_engine.php! Size: " . strlen($targetContent);
} else {
    echo "Could not find it.";
}
