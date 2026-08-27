<?php
$file = __DIR__ . '/resources/views/kids/mission/engine.blade.php';
$content = file_get_contents($file);

$cleanCss = <<<CSS
    /* ---- ANSWER GRID ---- */
    .answer-zone { flex: 1; padding: var(--kid-space-3) var(--kid-space-5) var(--kid-space-5); }
    .answer-grid { display: grid; gap: var(--kid-space-4); max-width: 540px; margin: 0 auto; }
    .answer-grid[data-count="2"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count="3"] { grid-template-columns: repeat(3, 1fr); }
    .answer-grid[data-count="4"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count="5"] { grid-template-columns: repeat(3, 1fr); }
    .answer-grid[data-count="6"] { grid-template-columns: repeat(3, 1fr); }
    
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

    /* ---- ANSWER CARD ---- */
    .answer-card {
        background: white; border-radius: var(--kid-radius-lg);
        border: 4px solid transparent; padding: var(--kid-space-5);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: var(--kid-space-2); min-height: 110px; cursor: pointer;
        font-family: var(--kid-font-heading);
        font-size: clamp(20px, 4vw, 28px);
        font-weight: 900; color: var(--kid-text);
        box-shadow: var(--kid-shadow-soft); position: relative; user-select: none;
        transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease, opacity 0.3s ease;
        touch-action: manipulation; opacity: 0;
        animation: kid-fade-slide-up 0.4s ease-out forwards;
    }
    .answer-card:nth-child(1) { animation-delay: 0.1s; }
    .answer-card:nth-child(2) { animation-delay: 0.18s; }
    .answer-card:nth-child(3) { animation-delay: 0.26s; }
    .answer-card:nth-child(4) { animation-delay: 0.34s; }
    .answer-card:nth-child(5) { animation-delay: 0.42s; }
    .answer-card:nth-child(6) { animation-delay: 0.50s; }

    .answer-card:not(.locked):hover {
        transform: translateY(-4px);
        border-color: #C4B5FD;
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.15);
    }
    .answer-card:not(.locked):active {
        transform: scale(0.95);
    }
    .answer-card.selected {
        border-color: var(--kid-primary, #7C3AED);
        background: #EDE9FE;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2);
    }
    .answer-card.correct {
        border-color: var(--kid-success, #22C55E);
        background: #DCFCE7;
        animation: celebrate-bounce 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .answer-card.correct .badge {
        position: absolute; top: -12px; right: -12px;
        width: 36px; height: 36px; border-radius: var(--kid-radius-full);
        background: var(--kid-success, #22C55E); color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: kid-pop-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s backwards;
    }
    .answer-card.incorrect {
        border-color: #E5E7EB; background: #F9FAFB; opacity: 0.4;
        animation: gentle-shake 0.4s ease;
    }
    .answer-card.locked { cursor: default; pointer-events: none; }
    .answer-card.reveal {
        border-color: var(--kid-success, #22C55E);
        background: #DCFCE7;
        animation: reveal-pulse 1s ease infinite;
    }

    @keyframes celebrate-bounce {
        0% { transform: scale(1); }
        25% { transform: scale(1.12) rotate(-2deg); }
        50% { transform: scale(0.96) rotate(1deg); }
        75% { transform: scale(1.03) rotate(-1deg); }
        100% { transform: scale(1) rotate(0); }
    }
    @keyframes gentle-shake {
        0%, 100% { transform: translateX(0); }
        20% { transform: translateX(-4px); }
        40% { transform: translateX(4px); }
        60% { transform: translateX(-2px); }
        80% { transform: translateX(2px); }
    }
    @keyframes reveal-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
        50% { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
    }
    @keyframes kid-bounce-in {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes kid-fade-slide-up {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    @keyframes kid-pop-in {
        0% { opacity: 0; transform: scale(0); }
        100% { opacity: 1; transform: scale(1); }
    }
    @keyframes kid-wiggle {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-3deg); }
        75% { transform: rotate(3deg); }
    }

    .answer-card .card-emoji { font-size: 36px; line-height: 1; }
    .answer-card .card-image { width: 48px; height: 48px; border-radius: 8px; object-fit: cover; }
CSS;

$pattern = '/\/\* ---- ANSWER GRID ---- \*\/(.*?)\/\* ---- FLASHCARD TYPE ---- \*\//s';
$content = preg_replace($pattern, $cleanCss . "\n\n    /* ---- FLASHCARD TYPE ---- */", $content);
file_put_contents($file, $content);

echo "CSS cleaned!\n";
