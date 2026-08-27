<?php
$file = __DIR__ . '/premium_style.css';
$css = file_get_contents($file);

// Add the colored buttons logic to the end of the file
$extraCss = <<<CSS

/* Colored borders for puzzle options and answer cards */
.puzzle-option:nth-child(3n+1), .answer-card:nth-child(3n+1) { border-color: var(--btn-green); color: var(--btn-green-dark); }
.puzzle-option:nth-child(3n+2), .answer-card:nth-child(3n+2) { border-color: var(--btn-blue); color: var(--btn-blue-dark); }
.puzzle-option:nth-child(3n+3), .answer-card:nth-child(3n+3) { border-color: var(--btn-red); color: var(--btn-red-dark); }
.puzzle-option:nth-child(4n), .answer-card:nth-child(4n) { border-color: var(--btn-yellow); color: var(--btn-yellow-dark); }

/* Remove default background if any */
.puzzle-option, .answer-card { background: white; }

CSS;

if (strpos($css, 'Colored borders for puzzle options') === false) {
    file_put_contents($file, $css . "\n" . $extraCss);
}
