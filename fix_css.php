<?php
$c = file_get_contents('resources/views/kids/mission/engine.blade.php');
$css = "
    .question-badge {
        display: inline-block; background: #EDE9FE; color: #6B21A8;
        font-weight: 700; padding: 6px 16px; border-radius: var(--kid-radius-full);
        font-size: 14px; margin-bottom: 12px;
    }
    .question-image {
        max-height: 35vh; border-radius: var(--kid-radius-lg);
        margin: 12px auto; box-shadow: var(--kid-shadow-soft);
        object-fit: contain; width: auto; max-width: 100%;
    }

    /* ---- ANSWER GRID ---- */
    .answer-zone { flex: 1; padding: var(--kid-space-3) var(--kid-space-5) var(--kid-space-5); }
    .answer-grid { display: grid; gap: var(--kid-space-4); max-width: 540px; margin: 0 auto; }
    .answer-grid[data-count=\"2\"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count=\"3\"] { grid-template-columns: repeat(3, 1fr); }
    .answer-grid[data-count=\"4\"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count=\"5\"] { grid-template-columns: repeat(3, 1fr); }
    .answer-grid[data-count=\"6\"] { grid-template-columns: repeat(3, 1fr); }
    
    /* ADAPTIVE LONG TEXT MODE */
    .answer-grid.long-text-mode {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }
    .answer-grid.long-text-mode .answer-card {
        width: 100%;
        text-align: left;
        min-height: 60px;
        padding: 16px 24px;
        align-items: flex-start;
        justify-content: flex-start;
    }

    /* ---- ANSWER CARD ---- */";

$c = str_replace("    /* ---- ANSWER CARD ---- */", $css, $c);
file_put_contents('resources/views/kids/mission/engine.blade.php', $c);
echo "Fixed CSS!";
