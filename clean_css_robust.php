<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

if (preg_match('/<style>(.*?)<\/style>/s', $content, $matches)) {
    $css = $matches[1];
    
    // Split the CSS into parts based on the headers
    // The pattern matches /* ---- HEADER ---- */
    $parts = preg_split('/\/\*\s*----\s*(.*?)\s*----\s*\*\//', $css, -1, PREG_SPLIT_DELIM_CAPTURE);
    
    $cleanCss = $parts[0]; // the initial parts before any header
    $seen = [];
    
    for ($i = 1; $i < count($parts); $i += 2) {
        $header = trim($parts[$i]);
        $body = $parts[$i+1];
        
        if (!isset($seen[$header])) {
            $seen[$header] = true;
            $cleanCss .= "    /* ---- $header ---- */" . $body;
        }
    }
    
    // Replace the old style block
    $newContent = preg_replace('/<style>.*?<\/style>/s', "<style>" . $cleanCss . "</style>", $content);
    file_put_contents($file, $newContent);
    echo "CSS deduplicated and saved successfully!";
} else {
    echo "Could not find <style> block.";
}
