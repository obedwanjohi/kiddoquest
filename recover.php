<?php
$transcript = 'C:/Users/livewave/.gemini/antigravity/brain/f4af740d-d30f-425a-94e4-182f02b06858/.system_generated/logs/transcript.jsonl';
$lines = file($transcript);
$lastContent = null;

foreach ($lines as $line) {
    $data = json_decode($line, true);
    if (isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $tc) {
            if ($tc['name'] === 'write_to_file') {
                $args = $tc['args'];
                // The args are usually a JSON string in 'content' or an array depending on the agent framework.
                // Wait, in transcript.jsonl, args is an array or object.
                if (isset($args['TargetFile']) && strpos($args['TargetFile'], 'engine.blade.php') !== false) {
                    if (isset($args['CodeContent'])) {
                        $lastContent = $args['CodeContent'];
                    }
                }
            }
        }
    }
}

if ($lastContent) {
    file_put_contents('recovery.txt', $lastContent);
    echo "Recovered content! Length: " . strlen($lastContent) . "\n";
} else {
    echo "Could not find write_to_file for engine.blade.php in transcript.\n";
}
