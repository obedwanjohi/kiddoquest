<?php
$file = __DIR__ . '/clean_style2.css';
$content = file_get_contents($file);

// The bad chunk starts at `.puzzle-option` and ends at the end of `kid-fade-slide-up`
$pattern = '/\.puzzle-option \{.*?@keyframes kid-fade-slide-up \{.*?\}\s*\}/s';

$replacement = <<<CSS
    .puzzle-option {
        width: 64px; height: 72px; display: flex; align-items: center; justify-content: center;
        background: white; border: 3px solid #E5E7EB; border-radius: var(--kid-radius-md);
        font-family: var(--kid-font-heading); font-size: 32px; font-weight: 900;
        color: var(--kid-text); cursor: pointer;
        box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        transition: all 0.2s ease;
    }
    .puzzle-option:hover { transform: translateY(-4px); border-color: #C4B5FD; box-shadow: 0 8px 0 rgba(124, 58, 237, 0.2); }
    .puzzle-option:active { transform: translateY(0); box-shadow: 0 2px 0 rgba(0,0,0,0.1); }
    .puzzle-option.used { opacity: 0.3; pointer-events: none; transform: scale(0.9); }
CSS;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
echo "Fixed FILL BLANK syntax error.";
