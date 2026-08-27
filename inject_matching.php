<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$matchingCss = <<<CSS
    /* ---- MATCHING TYPE ---- */
    .matching-board {
        display: grid; grid-template-columns: 1fr 1fr; gap: var(--kid-space-4);
        max-width: 540px; margin: 0 auto;
    }
    .matching-column { display: flex; flex-direction: column; gap: var(--kid-space-3); }
    .matching-item {
        flex: 1;
        background: white; border-radius: var(--kid-radius-md);
        border: 4px solid transparent; padding: var(--kid-space-3) var(--kid-space-4);
        font-family: var(--kid-font-heading); font-size: 32px; font-weight: 900;
        text-align: center; cursor: pointer; box-shadow: var(--kid-shadow-soft);
        transition: all 0.15s ease; touch-action: manipulation; user-select: none;
        animation: kid-fade-slide-up 0.4s ease-out forwards; opacity: 0; min-height: 56px;
        display: flex; align-items: center; justify-content: center;
    }
    .matching-item:nth-child(1) { animation-delay: 0.1s; }
    .matching-item:nth-child(2) { animation-delay: 0.18s; }
    .matching-item:nth-child(3) { animation-delay: 0.26s; }
    .matching-item:nth-child(4) { animation-delay: 0.34s; }
    .matching-item:nth-child(5) { animation-delay: 0.42s; }

    .matching-item:not(.matched):not(.locked):hover {
        transform: translateY(-3px); border-color: #C4B5FD;
        box-shadow: 0 6px 16px rgba(124, 58, 237, 0.15);
    }
    .matching-item:not(.matched):not(.locked):active { transform: scale(0.95); }
    .matching-item.selected {
        border-color: var(--kid-primary, #7C3AED); background: #EDE9FE;
        transform: scale(1.05);
    }
    .matching-item.matched {
        border-color: var(--kid-success, #22C55E); background: #DCFCE7;
        cursor: default; pointer-events: none;
        animation: celebrate-bounce 0.4s ease;
    }
    .matching-item.wrong {
        border-color: #EF4444; background: #FEE2E2;
        animation: gentle-shake 0.4s ease;
    }
    .matching-item.locked { cursor: default; pointer-events: none; opacity: 0.4; }
    .matching-progress {
        text-align: center; margin-bottom: var(--kid-space-3);
        font-family: var(--kid-font-heading); font-weight: 700;
        color: var(--kid-text-muted); font-size: 18px;
    }
    .matching-progress .check { color: var(--kid-success, #22C55E); font-size: 24px; }


CSS;

$content = str_replace('/* ---- FLASHCARD TYPE ---- */', $matchingCss . '/* ---- FLASHCARD TYPE ---- */', $content);
file_put_contents($file, $content);
echo "Matching CSS injected!";
