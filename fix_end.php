<?php
$file = __DIR__ . '/clean_style.css';
$content = file_get_contents($file);

// Replace from IDLE HINT to the end
$pattern = '/\/\* ---- IDLE HINT ---- \*\/.*/s';

$replacement = <<<CSS
    /* ---- IDLE HINT ---- */
    .idle-hint {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        background: var(--kid-primary, #7C3AED); color: white;
        padding: var(--kid-space-3) var(--kid-space-5);
        border-radius: var(--kid-radius-full);
        font-family: var(--kid-font-heading); font-weight: 700; font-size: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2); z-index: 55;
        display: none; animation: kid-pop-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .idle-hint.visible { display: block; }

    @media (max-width: 768px) {
        .idle-hint { display: none !important; }
    }

    [x-cloak] { display: none !important; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
CSS;

$content = preg_replace($pattern, $replacement, $content);
file_put_contents($file, $content);
