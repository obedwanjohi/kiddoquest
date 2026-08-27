@extends('kids.layouts.app')

@section('title', 'Quiz Player Prototype — Dev Test')

@php $kidTheme = 'forest'; @endphp

@push('styles')
<style>
    .proto-device-frame {
        max-width: 960px;
        margin: 0 auto;
        background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));
        border-radius: var(--kid-radius-xl);
        overflow: hidden;
        box-shadow: var(--kid-shadow-popup);
        position: relative;
    }
    .proto-type-selector {
        display: flex; gap: var(--kid-space-2); flex-wrap: wrap;
        justify-content: center; padding: var(--kid-space-4);
        background: rgba(255,255,255,0.6); backdrop-filter: blur(8px);
    }
    .proto-type-btn {
        padding: var(--kid-space-2) var(--kid-space-4);
        border-radius: var(--kid-radius-full);
        font-family: var(--kid-font-heading); font-weight: var(--kid-weight-bold);
        font-size: var(--kid-text-caption); background: white;
        border: 2px solid transparent; cursor: pointer;
        transition: all var(--kid-transition-fast); touch-action: manipulation;
    }
    .proto-type-btn:hover { transform: scale(1.05); }
    .proto-type-btn.active {
        background: var(--kid-primary); color: white;
        border-color: var(--kid-primary-dark); box-shadow: var(--kid-shadow-3d);
    }
    .quiz-shell { display: flex; flex-direction: column; min-height: 500px; position: relative; }
    .quiz-main {
        flex: 1; display: flex; flex-direction: column;
        justify-content: center; align-items: center;
        padding: var(--kid-space-5); gap: var(--kid-space-5);
    }
    .quiz-footer {
        padding: var(--kid-space-4) var(--kid-space-5); display: flex;
        justify-content: center; min-height: 88px; align-items: center;
    }
    .question-prompt {
        font-family: var(--kid-font-heading); font-size: var(--kid-text-question);
        font-weight: var(--kid-weight-bold); color: var(--kid-text);
        text-align: center; line-height: var(--kid-leading-snug);
    }
    .answer-grid { display: grid; gap: var(--kid-space-4); width: 100%; max-width: 600px; }
    .answer-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    .answer-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
    .answer-grid.cols-4 { grid-template-columns: repeat(4, 1fr); }
    @media (max-width: 640px) {
        .answer-grid.cols-3, .answer-grid.cols-4 { grid-template-columns: repeat(2, 1fr); }
    }
    .answer-card {
        background: var(--kid-bg-card); border-radius: var(--kid-radius-lg);
        border: 3px solid transparent; padding: var(--kid-space-5);
        display: flex; align-items: center; justify-content: center;
        font-family: var(--kid-font-heading); font-size: var(--kid-text-title);
        font-weight: var(--kid-weight-black); color: var(--kid-text);
        cursor: pointer; transition: all var(--kid-transition-fast);
        box-shadow: var(--kid-shadow-soft); min-height: 80px;
        position: relative; user-select: none; touch-action: manipulation;
    }
    .answer-card:hover:not(.locked) { transform: translateY(-2px) scale(1.02); border-color: var(--kid-primary-light); }
    .answer-card:active:not(.locked) { transform: scale(0.98); }
    .answer-card.selected { border-color: var(--kid-primary); background: #EDE9FE; }
    .answer-card.correct { border-color: var(--kid-success); background: var(--kid-success-light); animation: bounce-celebrate 0.5s var(--kid-ease-spring); }
    /* GENTLE wrong: muted gray, NOT red. Fades to show it's not the answer. */
    .answer-card.incorrect { border-color: var(--kid-border); background: #F3F4F6; opacity: 0.45; animation: shake-gentle 0.4s ease; }
    .answer-card.locked { cursor: default; }
    .answer-card.locked.incorrect { opacity: 0.45; }
    .answer-card.reveal { border-color: var(--kid-success); background: var(--kid-success-light); animation: pulse-reveal 1s ease infinite; }
    .answer-card .check-icon { position: absolute; top: var(--kid-space-2); right: var(--kid-space-2); font-size: 24px; }

    .tf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--kid-space-5); width: 100%; max-width: 600px; }
    .tf-btn {
        border-radius: var(--kid-radius-xl); min-height: 160px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: var(--kid-space-3); font-family: var(--kid-font-heading);
        font-size: var(--kid-text-hero); font-weight: var(--kid-weight-black);
        cursor: pointer; transition: all var(--kid-transition-fast);
        border: 4px solid transparent; box-shadow: var(--kid-shadow-soft); touch-action: manipulation;
    }
    .tf-btn.true-btn { background: #DCFCE7; color: #16A34A; }
    /* False button: warm amber, NOT red — it's a choice, not an error */
    .tf-btn.false-btn { background: #FEF3C7; color: #D97706; }
    .tf-btn:hover:not(.locked) { transform: scale(1.03); box-shadow: var(--kid-shadow-medium); }
    .tf-btn:active:not(.locked) { transform: scale(0.97); }
    .tf-btn.correct { animation: bounce-celebrate 0.5s var(--kid-ease-spring); }
    .tf-btn.incorrect { opacity: 0.45; animation: shake-gentle 0.4s ease; }
    .tf-btn.locked { cursor: default; }
    .tf-btn.reveal { animation: pulse-reveal 1s ease infinite; }
    .tf-btn .tf-icon { font-size: 48px; }

    .audio-btn-container { display: flex; flex-direction: column; align-items: center; gap: var(--kid-space-3); }
    .audio-btn {
        width: 120px; height: 120px; border-radius: var(--kid-radius-full);
        background: var(--kid-primary); color: white; display: flex;
        align-items: center; justify-content: center; font-size: 48px;
        cursor: pointer; border: none; box-shadow: var(--kid-shadow-3d);
        transition: all var(--kid-transition-fast); touch-action: manipulation;
    }
    .audio-btn:hover { transform: scale(1.05); }
    .audio-btn:active { transform: scale(0.95); box-shadow: var(--kid-shadow-3d-pressed); }
    .audio-btn.playing { animation: audio-pulse 1s ease infinite; }
    @keyframes audio-pulse {
        0%, 100% { transform: scale(1); box-shadow: var(--kid-shadow-3d); }
        50% { transform: scale(1.1); box-shadow: 0 6px 0 rgba(0,0,0,0.15), 0 0 20px var(--kid-primary-light); }
    }

    .count-image-area {
        background: var(--kid-bg-card); border-radius: var(--kid-radius-lg);
        padding: var(--kid-space-6); min-height: 200px; display: flex;
        align-items: center; justify-content: center; font-size: 48px;
        gap: var(--kid-space-3); flex-wrap: wrap; max-width: 600px;
        width: 100%; box-shadow: var(--kid-shadow-soft);
    }
    .count-btn-grid { display: flex; gap: var(--kid-space-3); flex-wrap: wrap; justify-content: center; }
    .count-btn {
        width: 80px; height: 80px; border-radius: var(--kid-radius-md);
        background: var(--kid-bg-card); border: 3px solid transparent;
        font-family: var(--kid-font-heading); font-size: var(--kid-text-title);
        font-weight: var(--kid-weight-black); cursor: pointer;
        box-shadow: var(--kid-shadow-soft); transition: all var(--kid-transition-fast); touch-action: manipulation;
    }
    .count-btn:hover:not(.locked) { transform: translateY(-2px); border-color: var(--kid-primary-light); }
    .count-btn:active:not(.locked) { transform: scale(0.95); }
    .count-btn.correct { border-color: var(--kid-success); background: var(--kid-success-light); animation: bounce-celebrate 0.5s var(--kid-ease-spring); }
    .count-btn.incorrect { border-color: var(--kid-border); opacity: 0.45; animation: shake-gentle 0.4s ease; }
    .count-btn.locked { cursor: default; }
    .count-btn.reveal { border-color: var(--kid-success); background: var(--kid-success-light); animation: pulse-reveal 1s ease infinite; }

    .pattern-row { display: flex; align-items: center; justify-content: center; gap: var(--kid-space-3); flex-wrap: wrap; }
    .pattern-item {
        width: 64px; height: 64px; border-radius: var(--kid-radius-md);
        background: var(--kid-bg-card); display: flex; align-items: center;
        justify-content: center; font-size: 36px; box-shadow: var(--kid-shadow-soft);
    }
    .pattern-item.mystery { border: 3px dashed var(--kid-primary); background: transparent; animation: pulse-mystery 1.5s ease infinite; }
    @keyframes pulse-mystery { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.08); opacity: 0.7; } }
    .pattern-options { display: flex; gap: var(--kid-space-4); justify-content: center; }
    .pattern-option {
        width: 100px; height: 100px; border-radius: var(--kid-radius-lg);
        background: var(--kid-bg-card); border: 3px solid transparent;
        display: flex; align-items: center; justify-content: center;
        font-size: 48px; cursor: pointer; box-shadow: var(--kid-shadow-soft);
        transition: all var(--kid-transition-fast); touch-action: manipulation;
    }
    .pattern-option:hover:not(.locked) { transform: translateY(-2px) scale(1.05); border-color: var(--kid-primary-light); }
    .pattern-option:active:not(.locked) { transform: scale(0.95); }
    .pattern-option.correct { border-color: var(--kid-success); background: var(--kid-success-light); animation: bounce-celebrate 0.5s var(--kid-ease-spring); }
    .pattern-option.incorrect { border-color: var(--kid-border); opacity: 0.45; animation: shake-gentle 0.4s ease; }
    .pattern-option.locked { cursor: default; }
    .pattern-option.reveal { border-color: var(--kid-success); background: var(--kid-success-light); animation: pulse-reveal 1s ease infinite; }

    .memory-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: var(--kid-space-3); max-width: 480px; }
    @media (max-width: 480px) { .memory-grid { grid-template-columns: repeat(3, 1fr); } }
    .memory-card { aspect-ratio: 1; perspective: 600px; cursor: pointer; }
    .memory-card-inner { width: 100%; height: 100%; position: relative; transform-style: preserve-3d; transition: transform 0.4s var(--kid-ease-spring); }
    .memory-card.flipped .memory-card-inner, .memory-card.matched .memory-card-inner { transform: rotateY(180deg); }
    .memory-card-face {
        position: absolute; inset: 0; backface-visibility: hidden;
        border-radius: var(--kid-radius-md); display: flex; align-items: center;
        justify-content: center; font-size: 32px; font-family: var(--kid-font-heading); font-weight: var(--kid-weight-black);
    }
    .memory-card-back { background: linear-gradient(135deg, var(--kid-primary), var(--kid-primary-dark)); color: white; }
    .memory-card-front { background: var(--kid-bg-card); color: var(--kid-text); transform: rotateY(180deg); box-shadow: var(--kid-shadow-soft); }
    .memory-card.matched .memory-card-front { background: var(--kid-success-light); border: 3px solid var(--kid-success); animation: match-celebrate 0.5s var(--kid-ease-spring); }
    .memory-card.locked { pointer-events: none; }
    @keyframes match-celebrate {
        0% { transform: rotateY(180deg) scale(1); }
        50% { transform: rotateY(180deg) scale(1.15); }
        100% { transform: rotateY(180deg) scale(1); }
    }

    .feedback-overlay {
        position: absolute; inset: 0; background: rgba(0,0,0,0.2);
        backdrop-filter: blur(3px); display: flex; align-items: center;
        justify-content: center; z-index: 50;
    }
    .feedback-box {
        background: white; border-radius: var(--kid-radius-xl);
        padding: var(--kid-space-6); text-align: center;
        box-shadow: var(--kid-shadow-popup); animation: feedback-pop 0.3s var(--kid-ease-spring);
    }
    @keyframes feedback-pop { 0% { transform: scale(0.5); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    .feedback-emoji { font-size: 64px; line-height: 1; }
    .feedback-text { font-family: var(--kid-font-heading); font-size: var(--kid-text-title); font-weight: var(--kid-weight-black); margin-top: var(--kid-space-2); }
    .feedback-text.correct { color: var(--kid-success); }
    /* Wrong feedback: warm encouraging amber, never red */
    .feedback-text.wrong { color: var(--kid-encourage-dark); }

    /* GENTLE animations — per Kid Interaction Guidelines Section 1 */
    @keyframes bounce-celebrate {
        0% { transform: scale(1); }
        30% { transform: scale(1.15) rotate(-2deg); }
        60% { transform: scale(0.97) rotate(2deg); }
        100% { transform: scale(1) rotate(0); }
    }
    /* Gentle shake: only 4px (not 8px), per guidelines */
    @keyframes shake-gentle { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
    /* Reveal pulse: draws attention to correct answer after 3 wrong tries */
    @keyframes pulse-reveal {
        0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.4); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
    }

    .quiz-next-btn {
        padding: var(--kid-space-4) var(--kid-space-7);
        border-radius: var(--kid-radius-full); background: var(--kid-primary);
        color: white; font-family: var(--kid-font-heading);
        font-size: var(--kid-text-mission); font-weight: var(--kid-weight-black);
        border: none; cursor: pointer; box-shadow: var(--kid-shadow-3d);
        transition: all var(--kid-transition-fast); touch-action: manipulation;
        min-height: var(--kid-touch-large);
    }
    .quiz-next-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 0 rgba(0,0,0,0.15); }
    .quiz-next-btn:active { transform: translateY(2px); box-shadow: var(--kid-shadow-3d-pressed); }

    /* Leo hint bubble — appears on wrong answers */
    .leo-hint {
        background: var(--kid-bg-card);
        border-radius: var(--kid-radius-lg);
        padding: var(--kid-space-3) var(--kid-space-4);
        font-family: var(--kid-font-heading);
        font-weight: var(--kid-weight-medium);
        font-size: var(--kid-text-body);
        color: var(--kid-encourage-dark);
        box-shadow: var(--kid-shadow-soft);
        animation: feedback-pop 0.3s var(--kid-ease-spring);
        max-width: 400px;
        text-align: center;
    }

    .device-toggle { display: flex; justify-content: center; gap: var(--kid-space-2); margin-bottom: var(--kid-space-4); }
    .device-toggle button {
        padding: var(--kid-space-2) var(--kid-space-4);
        border-radius: var(--kid-radius-full); font-family: var(--kid-font-heading);
        font-weight: var(--kid-weight-bold); font-size: var(--kid-text-caption);
        background: white; border: 2px solid var(--kid-border); cursor: pointer;
        transition: all var(--kid-transition-fast);
    }
    .device-toggle button.active { background: var(--kid-primary); color: white; border-color: var(--kid-primary); }

    .device-phone-portrait { max-width: 375px; min-height: 667px; }
    .device-phone-landscape { max-width: 667px; min-height: 375px; }
    .device-tablet { max-width: 768px; min-height: 500px; }
    .device-desktop { max-width: 960px; min-height: 600px; }

    .status-bar { text-align: center; padding: var(--kid-space-2); font-family: var(--kid-font-heading); font-size: var(--kid-text-caption); color: var(--kid-text-muted); background: rgba(255,255,255,0.8); }

    .progress-header { display: flex; align-items: center; gap: var(--kid-space-3); padding: var(--kid-space-3) var(--kid-space-4); background: rgba(255,255,255,0.7); }
    .progress-header .leo { font-size: 36px; }
    .progress-header .leo-bubble { flex: 1; background: var(--kid-bg-card); border-radius: var(--kid-radius-lg); padding: var(--kid-space-2) var(--kid-space-3); font-family: var(--kid-font-heading); font-weight: var(--kid-weight-medium); font-size: var(--kid-text-body); box-shadow: var(--kid-shadow-soft); }
    .progress-text { font-family: var(--kid-font-heading); font-weight: var(--kid-weight-bold); color: var(--kid-text-muted); font-size: var(--kid-text-caption); white-space: nowrap; }

    /* Reduced motion support — per Guidelines Section 10 */
    @media (prefers-reduced-motion: reduce) {
        .answer-card.correct, .tf-btn.correct, .count-btn.correct, .pattern-option.correct,
        .memory-card.matched .memory-card-front {
            animation: none !important;
        }
        .answer-card.incorrect, .tf-btn.incorrect, .count-btn.incorrect, .pattern-option.incorrect {
            animation: none !important;
        }
        .pattern-item.mystery, .audio-btn.playing, .reveal {
            animation: none !important;
        }
    }
</style>
@endpush

@section('kid-content')
<div x-data="quizPrototype()" x-cloak class="min-h-screen p-4">

    <div class="text-center mb-6">
        <h1 class="font-black text-3xl mb-2" style="font-family: var(--kid-font-heading); color: var(--kid-text);">
            🎮 Quiz Player Prototype
        </h1>
        <p class="text-sm" style="color: var(--kid-text-muted);">
            Visual test of all 6 Phase 1 question type renderers. Compliant with KID INTERACTION GUIDELINES.
        </p>
    </div>

    <div class="device-toggle">
        <button @click="deviceSize = 'phone-portrait'" :class="{ active: deviceSize === 'phone-portrait' }">📱 Portrait</button>
        <button @click="deviceSize = 'phone-landscape'" :class="{ active: deviceSize === 'phone-landscape' }">📱 Landscape</button>
        <button @click="deviceSize = 'tablet'" :class="{ active: deviceSize === 'tablet' }">📐 Tablet</button>
        <button @click="deviceSize = 'desktop'" :class="{ active: deviceSize === 'desktop' }">🖥️ Desktop</button>
    </div>

    <div class="mb-4" style="max-width: 960px; margin: 0 auto;">
        <div class="proto-type-selector">
            <button @click="switchType('multiple-choice')" :class="{ active: currentType === 'multiple-choice' }" class="proto-type-btn">👆 QT-01 Multiple Choice</button>
            <button @click="switchType('true-false')" :class="{ active: currentType === 'true-false' }" class="proto-type-btn">✅ QT-02 True/False</button>
            <button @click="switchType('listen-choose')" :class="{ active: currentType === 'listen-choose' }" class="proto-type-btn">🔊 QT-06 Listen & Choose</button>
            <button @click="switchType('count-objects')" :class="{ active: currentType === 'count-objects' }" class="proto-type-btn">🔢 QT-09 Count Objects</button>
            <button @click="switchType('complete-pattern')" :class="{ active: currentType === 'complete-pattern' }" class="proto-type-btn">🔁 QT-10 Complete Pattern</button>
            <button @click="switchType('memory-match')" :class="{ active: currentType === 'memory-match' }" class="proto-type-btn">🃏 QT-11 Memory Match</button>
        </div>
    </div>

    <div :class="'proto-device-frame device-' + deviceSize">

        <div class="flex items-center justify-between px-4 py-3 bg-white/80 shadow-sm">
            <button class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-xl">🗺️</button>
            <span class="font-black text-lg" style="font-family: var(--kid-font-heading); color: var(--kid-text);">Quiz Time!</span>
            <div class="flex items-center gap-1 px-3 py-1 rounded-full bg-yellow-100">
                <span class="text-lg">⭐</span>
                <span class="font-black" style="font-family: var(--kid-font-heading); color: #D97706;">12</span>
            </div>
        </div>

        <div class="progress-header">
            <span class="leo">🦁</span>
            <div class="leo-bubble"><span x-text="leoMessage"></span></div>
            <span class="progress-text" x-text="'Q' + (currentIndex + 1) + '/' + totalQuestions"></span>
        </div>

        <div class="quiz-shell">

            {{-- QT-01: Multiple Choice --}}
            <template x-if="currentType === 'multiple-choice'">
                <div class="quiz-main">
                    <div class="question-prompt" x-text="currentQuestion.prompt"></div>
                    <div class="answer-grid" :class="getGridClass(currentQuestion.options.length)">
                        <template x-for="(option, i) in currentQuestion.options" :key="i">
                            <div class="answer-card"
                                 :class="getCardClass(i, option.is_correct)"
                                 @click="selectAnswer(i)">
                                <span x-text="option.text"></span>
                                <template x-if="selectedIndex === i && option.is_correct && answered">
                                    <span class="check-icon">✅</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- QT-02: True / False --}}
            <template x-if="currentType === 'true-false'">
                <div class="quiz-main">
                    <div class="question-prompt" x-text="currentQuestion.prompt"></div>
                    <template x-if="currentQuestion.image">
                        <div class="text-6xl" x-text="currentQuestion.image"></div>
                    </template>
                    <div class="tf-grid">
                        <div class="tf-btn true-btn"
                             :class="getCardClass(0, currentQuestion.options[0].is_correct)"
                             @click="selectAnswer(0)">
                            <span class="tf-icon">✅</span><span>TRUE</span>
                        </div>
                        <div class="tf-btn false-btn"
                             :class="getCardClass(1, currentQuestion.options[1].is_correct)"
                             @click="selectAnswer(1)">
                            <span class="tf-icon">❌</span><span>FALSE</span>
                        </div>
                    </div>
                </div>
            </template>

            {{-- QT-06: Listen & Choose --}}
            <template x-if="currentType === 'listen-choose'">
                <div class="quiz-main">
                    <div class="question-prompt">🔊 Listen and choose!</div>
                    <div class="audio-btn-container">
                        <button class="audio-btn" :class="{ playing: isPlaying }" @click="playAudio">
                            <span x-text="isPlaying ? '🔊' : '▶️'"></span>
                        </button>
                        <span class="text-sm" style="color: var(--kid-text-muted);" x-text="currentQuestion.audioLabel"></span>
                    </div>
                    <div class="answer-grid" :class="getGridClass(currentQuestion.options.length)">
                        <template x-for="(option, i) in currentQuestion.options" :key="i">
                            <div class="answer-card"
                                 :class="getCardClass(i, option.is_correct)"
                                 @click="selectAnswer(i)">
                                <span x-text="option.text"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- QT-09: Count Objects --}}
            <template x-if="currentType === 'count-objects'">
                <div class="quiz-main">
                    <div class="question-prompt" x-text="currentQuestion.prompt"></div>
                    <div class="count-image-area">
                        <template x-for="(emoji, i) in currentQuestion.emojis" :key="i">
                            <span x-text="emoji"></span>
                        </template>
                    </div>
                    <div class="count-btn-grid">
                        <template x-for="(option, i) in currentQuestion.options" :key="i">
                            <button class="count-btn"
                                    :class="getCardClass(i, option.is_correct)"
                                    @click="selectAnswer(i)" x-text="option.text"></button>
                        </template>
                    </div>
                </div>
            </template>

            {{-- QT-10: Complete Pattern --}}
            <template x-if="currentType === 'complete-pattern'">
                <div class="quiz-main">
                    <div class="question-prompt">What comes next?</div>
                    <div class="pattern-row">
                        <template x-for="(item, i) in currentQuestion.sequence" :key="i">
                            <div class="pattern-item" x-text="item"></div>
                        </template>
                        <div class="pattern-item mystery">❓</div>
                    </div>
                    <div class="pattern-options">
                        <template x-for="(option, i) in currentQuestion.options" :key="i">
                            <div class="pattern-option"
                                 :class="getCardClass(i, option.is_correct)"
                                 @click="selectAnswer(i)">
                                <span x-text="option.text"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- QT-11: Memory Match --}}
            <template x-if="currentType === 'memory-match'">
                <div class="quiz-main">
                    <div class="question-prompt">Find the matching pairs!</div>
                    <div class="flex gap-6 items-center">
                        <div class="memory-grid">
                            <template x-for="(card, i) in memoryCards" :key="i">
                                <div class="memory-card"
                                     :class="{ flipped: card.flipped, matched: card.matched, locked: memoryLocked }"
                                     @click="flipCard(i)">
                                    <div class="memory-card-inner">
                                        <div class="memory-card-face memory-card-back">🦁</div>
                                        <div class="memory-card-face memory-card-front" x-text="card.content"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="text-center">
                            <div class="font-black text-2xl" style="font-family: var(--kid-font-heading); color: var(--kid-text);">
                                <span x-text="memoryPairs"></span> / <span x-text="memoryTotalPairs"></span>
                            </div>
                            <div class="text-sm mt-1" style="color: var(--kid-text-muted);">Pairs Found</div>
                            <div class="font-black text-lg mt-3" style="font-family: var(--kid-font-heading); color: var(--kid-primary);">
                                Flips: <span x-text="memoryFlips"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Leo Hint (appears on wrong answer, per Guidelines Section 8) --}}
            <template x-if="showHint">
                <div class="quiz-main" style="padding-top: 0;">
                    <div class="leo-hint">
                        🦁 <span x-text="hintMessage"></span>
                    </div>
                </div>
            </template>

            {{-- Feedback Overlay (only for CORRECT answers and milestones) --}}
            <template x-if="showFeedback">
                <div class="feedback-overlay" @click="dismissFeedback">
                    <div class="feedback-box">
                        <div class="feedback-emoji" x-text="feedbackData.emoji"></div>
                        <div class="feedback-text" :class="feedbackData.type" x-text="feedbackData.text"></div>
                    </div>
                </div>
            </template>

            {{-- Footer --}}
            <div class="quiz-footer">
                <template x-if="!answered && !showHint && currentType !== 'memory-match'">
                    <div class="text-center" style="color: var(--kid-text-muted); font-family: var(--kid-font-heading); font-size: var(--kid-text-body);">
                        👆 Tap an answer!
                    </div>
                </template>
                <template x-if="answered">
                    <button class="quiz-next-btn" @click="nextQuestion">
                        <span x-text="currentIndex + 1 >= totalQuestions ? '🎉 See Results!' : 'Next →'"></span>
                    </button>
                </template>
                <template x-if="currentType === 'memory-match' && memoryPairs === memoryTotalPairs && !answered">
                    <button class="quiz-next-btn" @click="memoryComplete()">
                        🎉 All Pairs Found!
                    </button>
                </template>
            </div>
        </div>

        <div class="status-bar">
            <span x-text="getTypeName()"></span> | Device: <span x-text="deviceSize"></span>
            <template x-if="wrongAttempts > 0 && !answered">
                <span> | Tries: <span x-text="wrongAttempts"></span>/3</span>
            </template>
        </div>
    </div>

    <div class="mt-8 max-w-3xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="font-black text-xl mb-3" style="font-family: var(--kid-font-heading);">📋 QA Checklist — Compliant with Kid Interaction Guidelines</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
            <div>✅ Correct answer turns green + bounces (0.5s)</div>
            <div>🔲 Wrong answer fades to gray + gentle shake (4px, not red!)</div>
            <div>🔄 Wrong answers allow retry (never lock child out)</div>
            <div>💡 After 3 wrong tries, correct answer reveals itself</div>
            <div>🦁 Leo gives encouragement on wrong answers</div>
            <div>👆 All touch targets ≥ 44px</div>
            <div>▶️ Next button appears after correct answer</div>
            <div>📱 Responsive at all device sizes</div>
            <div>♿ Reduced motion support via media query</div>
            <div>🎨 No harsh red anywhere except exit button</div>
        </div>
        <p class="mt-3 text-xs" style="color: var(--kid-text-muted);">
            See KID INTERACTION GUIDELINES.md for full rules. Test wrong answers to see gentle feedback.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function quizPrototype() {
    const questions = {
        'multiple-choice': {
            prompt: "Which letter is A?",
            encouragement: "You've got this!",
            options: [
                { text: "🅰️", is_correct: true },
                { text: "B", is_correct: false },
                { text: "C", is_correct: false },
                { text: "D", is_correct: false },
            ]
        },
        'true-false': {
            prompt: '"Banana" starts with the letter B.',
            image: "🍌",
            encouragement: "Is this true or false?",
            options: [
                { text: "TRUE", is_correct: true },
                { text: "FALSE", is_correct: false },
            ]
        },
        'listen-choose': {
            prompt: "🔊 Listen and choose!",
            encouragement: "Listen carefully!",
            audioLabel: "Sound: 'Ah' (Letter A)",
            options: [
                { text: "A", is_correct: true },
                { text: "B", is_correct: false },
                { text: "C", is_correct: false },
            ]
        },
        'count-objects': {
            prompt: "How many apples do you see?",
            encouragement: "Count them carefully!",
            emojis: ["🍎", "🍎", "🍎", "🍎", "🍎"],
            options: [
                { text: "3", is_correct: false },
                { text: "4", is_correct: false },
                { text: "5", is_correct: true },
                { text: "6", is_correct: false },
            ]
        },
        'complete-pattern': {
            prompt: "What comes next?",
            encouragement: "Look at the pattern!",
            sequence: ["🔴", "🔵", "🔴", "🔵", "🔴"],
            options: [
                { text: "🔴", is_correct: false },
                { text: "🔵", is_correct: true },
                { text: "🟢", is_correct: false },
            ]
        },
        'memory-match': {
            prompt: "Find the matching pairs!",
            encouragement: "Flip cards to find pairs!",
            pairs: [
                { content: "A", match_key: "a" },
                { content: "🍎", match_key: "a" },
                { content: "B", match_key: "b" },
                { content: "🍌", match_key: "b" },
                { content: "C", match_key: "c" },
                { content: "🐱", match_key: "c" },
            ]
        },
    };

    // Encouraging messages for wrong answers — per Guidelines Section 8
    const encouragementMessages = [
        "Almost! Try again!",
        "Good try! You can do it!",
        "Not quite — look carefully!",
    ];

    return {
        deviceSize: 'tablet',
        currentType: 'multiple-choice',
        currentQuestion: questions['multiple-choice'],
        currentIndex: 0,
        totalQuestions: 5,
        selectedIndex: null,
        answered: false,
        showFeedback: false,
        feedbackData: {},
        isPlaying: false,
        // Wrong answer protocol state (Section 8)
        wrongAttempts: 0,
        wrongIndices: [],
        showHint: false,
        hintMessage: '',
        leoMessage: "You've got this!",
        // Memory match state
        memoryCards: [],
        memoryFlippedIndices: [],
        memoryPairs: 0,
        memoryTotalPairs: 3,
        memoryFlips: 0,
        memoryLocked: false,

        init() { this.initMemory(); },

        switchType(type) {
            this.currentType = type;
            this.currentQuestion = questions[type];
            this.resetQuestion();
            if (type === 'memory-match') { this.initMemory(); }
        },

        resetQuestion() {
            this.selectedIndex = null;
            this.answered = false;
            this.showFeedback = false;
            this.showHint = false;
            this.wrongAttempts = 0;
            this.wrongIndices = [];
            this.isPlaying = false;
            this.leoMessage = this.currentQuestion.encouragement;
        },

        getGridClass(count) {
            if (count <= 2) return 'cols-2';
            if (count === 3) return 'cols-3';
            return 'cols-4';
        },

        // Centralized card class logic for all answer types
        getCardClass(index, isCorrectOption) {
            if (this.answered && this.selectedIndex === index && isCorrectOption) return 'correct locked';
            if (this.wrongIndices.includes(index)) return 'incorrect locked';
            // After 3 wrong attempts, reveal the correct answer
            if (this.wrongAttempts >= 3 && isCorrectOption && !this.answered) return 'reveal locked';
            if (this.answered || this.wrongAttempts >= 3) return 'locked';
            return '';
        },

        selectAnswer(index) {
            if (this.answered) return;
            if (this.wrongIndices.includes(index)) return; // Can't re-tap a dimmed wrong answer

            const option = this.currentQuestion.options[index];

            if (option.is_correct) {
                // CORRECT — celebrate!
                this.selectedIndex = index;
                this.answered = true;
                this.showHint = false;
                this.leoMessage = "Great job!";

                this.feedbackData = {
                    emoji: '🎉',
                    text: this.wrongAttempts === 0 ? "Perfect!" : "You got it!",
                    type: 'correct'
                };

                // Mini celebration (not over-celebration, per Section 7)
                setTimeout(() => { this.showFeedback = true; }, 500);

            } else {
                // WRONG — gentle, never harsh (per Section 8)
                this.wrongAttempts++;
                this.wrongIndices.push(index);
                this.showHint = true;
                this.hintMessage = encouragementMessages[Math.min(this.wrongAttempts - 1, 2)];
                this.leoMessage = this.hintMessage;

                if (this.wrongAttempts >= 3) {
                    // Reveal the correct answer after 3 tries
                    this.answered = true;
                    this.showHint = false;
                    setTimeout(() => {
                        this.leoMessage = "Here's the answer! Let's keep going!";
                        this.feedbackData = {
                            emoji: '💪',
                            text: "That's okay! Let's try the next one!",
                            type: 'wrong'
                        };
                        this.showFeedback = true;
                    }, 600);
                }
            }
        },

        dismissFeedback() { this.showFeedback = false; },

        nextQuestion() {
            this.resetQuestion();
            if (this.currentType === 'memory-match') { this.initMemory(); }
        },

        playAudio() {
            this.isPlaying = true;
            setTimeout(() => { this.isPlaying = false; }, 2000);
        },

        initMemory() {
            const shuffled = [...questions['memory-match'].pairs].sort(() => Math.random() - 0.5);
            this.memoryCards = shuffled.map(c => ({ ...c, flipped: false, matched: false }));
            this.memoryFlippedIndices = [];
            this.memoryPairs = 0;
            this.memoryTotalPairs = 3;
            this.memoryFlips = 0;
            this.memoryLocked = false;
        },

        flipCard(index) {
            if (this.memoryLocked) return;
            if (this.memoryCards[index].flipped || this.memoryCards[index].matched) return;
            this.memoryCards[index].flipped = true;
            this.memoryFlippedIndices.push(index);
            this.memoryFlips++;
            if (this.memoryFlippedIndices.length === 2) {
                this.memoryLocked = true;
                const [i1, i2] = this.memoryFlippedIndices;
                if (this.memoryCards[i1].match_key === this.memoryCards[i2].match_key) {
                    setTimeout(() => {
                        this.memoryCards[i1].matched = true;
                        this.memoryCards[i2].matched = true;
                        this.memoryPairs++;
                        this.memoryFlippedIndices = [];
                        this.memoryLocked = false;
                    }, 500);
                } else {
                    setTimeout(() => {
                        this.memoryCards[i1].flipped = false;
                        this.memoryCards[i2].flipped = false;
                        this.memoryFlippedIndices = [];
                        this.memoryLocked = false;
                    }, 800);
                }
            }
        },

        memoryComplete() {
            this.answered = true;
            this.showFeedback = true;
            this.feedbackData = { emoji: '🎉', text: 'You found them all!', type: 'correct' };
        },

        getTypeName() {
            const names = {
                'multiple-choice': 'QT-01 Multiple Choice',
                'true-false': 'QT-02 True/False',
                'listen-choose': 'QT-06 Listen & Choose',
                'count-objects': 'QT-09 Count Objects',
                'complete-pattern': 'QT-10 Complete Pattern',
                'memory-match': 'QT-11 Memory Match',
            };
            return names[this.currentType] || '';
        }
    };
}
</script>
@endpush