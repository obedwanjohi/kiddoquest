@extends('kids.layouts.app')

@section('title', 'QT-01 Multiple Choice — Polished Prototype')

@php $kidTheme = 'forest'; @endphp

@push('styles')
<style>
    /* ============================================================
       QT-01 POLISHED PROTOTYPE
       One question type. Perfected. Every layer wired.
       ============================================================ */

    .quiz-stage {
        max-width: 720px; margin: 0 auto; min-height: 100vh;
        display: flex; flex-direction: column; position: relative;
    }

    /* ---- HEADER: Progress + Stars + Exit ---- */
    .quiz-header {
        display: flex; align-items: center; gap: var(--kid-space-3);
        padding: var(--kid-space-3) var(--kid-space-4);
        background: rgba(255,255,255,0.9); backdrop-filter: blur(8px);
        position: sticky; top: 0; z-index: var(--kid-z-header);
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .quiz-header .exit-btn {
        width: 44px; height: 44px; border-radius: var(--kid-radius-full);
        background: var(--kid-bg-card); border: none; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; box-shadow: var(--kid-shadow-soft);
        transition: transform var(--kid-transition-fast); touch-action: manipulation;
    }
    .quiz-header .exit-btn:hover { transform: scale(1.1); }
    .quiz-header .exit-btn:active { transform: scale(0.92); }

    .quiz-header .progress-wrap { flex: 1; }
    .quiz-header .progress-bar-bg {
        height: 16px; background: #E5E7EB; border-radius: var(--kid-radius-full);
        overflow: hidden; position: relative;
    }
    .quiz-header .progress-bar-fill {
        height: 100%; background: linear-gradient(90deg, var(--kid-primary), var(--kid-primary-light));
        border-radius: var(--kid-radius-full);
        transition: width 0.5s var(--kid-ease-spring);
        position: relative;
    }
    .quiz-header .progress-bar-fill::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        animation: shimmer 2s ease infinite;
    }
    @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
    .quiz-header .progress-label {
        text-align: center; font-family: var(--kid-font-heading);
        font-weight: var(--kid-weight-bold); font-size: var(--kid-text-caption);
        color: var(--kid-text-muted); margin-top: 2px;
    }

    .quiz-header .star-pill {
        display: flex; align-items: center; gap: 4px;
        padding: 8px 14px; border-radius: var(--kid-radius-full);
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        font-family: var(--kid-font-heading); font-weight: var(--kid-weight-black);
        font-size: var(--kid-text-counter); color: var(--kid-encourage-dark);
        box-shadow: var(--kid-shadow-soft); transition: transform 0.3s var(--kid-ease-spring);
    }
    .quiz-header .star-pill.pulse { transform: scale(1.2); }
    .quiz-header .star-pill .star-icon { font-size: 20px; }

    /* ---- LEO MASCOT BUBBLE ---- */
    .leo-zone {
        display: flex; align-items: flex-start; gap: var(--kid-space-3);
        padding: var(--kid-space-4); animation: kid-bounce-in 0.5s var(--kid-ease-spring);
    }
    .leo-mascot {
        font-size: 48px; flex-shrink: 0; animation: kid-wiggle 3s ease-in-out infinite;
        transition: transform 0.3s var(--kid-ease-spring); cursor: default;
    }
    .leo-mascot.celebrating { animation: kid-celebrate 0.5s var(--kid-ease-spring); }
    .leo-bubble {
        background: var(--kid-bg-card); border-radius: var(--kid-radius-lg);
        padding: var(--kid-space-3) var(--kid-space-4); position: relative;
        box-shadow: var(--kid-shadow-soft); flex: 1; max-width: 320px;
        font-family: var(--kid-font-heading); font-weight: var(--kid-weight-medium);
        font-size: var(--kid-text-body); color: var(--kid-text); line-height: var(--kid-leading-snug);
    }
    .leo-bubble::before {
        content: ''; position: absolute; left: -10px; top: 16px;
        width: 0; height: 0; border-top: 6px solid transparent;
        border-bottom: 6px solid transparent; border-right: 10px solid var(--kid-bg-card);
    }
    .leo-bubble.encouraging { color: var(--kid-encourage-dark); }

    /* ---- QUESTION PROMPT ---- */
    .question-zone {
        text-align: center; padding: var(--kid-space-4) var(--kid-space-5);
    }
    .question-prompt {
        font-family: var(--kid-font-heading); font-size: var(--kid-text-question);
        font-weight: var(--kid-weight-black); color: var(--kid-text);
        line-height: var(--kid-leading-snug);
        animation: kid-fade-slide-up 0.4s var(--kid-ease-out);
    }

    /* ---- ANSWER GRID ---- */
    .answer-zone { flex: 1; padding: var(--kid-space-3) var(--kid-space-5) var(--kid-space-5); }
    .answer-grid { display: grid; gap: var(--kid-space-4); max-width: 540px; margin: 0 auto; }
    .answer-grid[data-count="2"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count="3"] { grid-template-columns: repeat(3, 1fr); }
    .answer-grid[data-count="4"] { grid-template-columns: repeat(2, 1fr); }
    .answer-grid[data-count="5"], .answer-grid[data-count="6"] { grid-template-columns: repeat(3, 1fr); }
    @media (max-width: 480px) {
        .answer-grid[data-count="3"], .answer-grid[data-count="5"], .answer-grid[data-count="6"] {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* ---- THE ANSWER CARD (polished to perfection) ---- */
    .answer-card {
        background: var(--kid-bg-card); border-radius: var(--kid-radius-lg);
        border: 4px solid transparent; padding: var(--kid-space-5);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: var(--kid-space-2); min-height: 110px; cursor: pointer;
        font-family: var(--kid-font-heading); font-size: var(--kid-text-title);
        font-weight: var(--kid-weight-black); color: var(--kid-text);
        box-shadow: var(--kid-shadow-soft); position: relative; user-select: none;
        transition: transform 0.15s var(--kid-ease-out),
                    border-color 0.15s var(--kid-ease-out),
                    box-shadow 0.15s var(--kid-ease-out),
                    opacity 0.3s var(--kid-ease-out);
        touch-action: manipulation; opacity: 0;
        animation: kid-fade-slide-up 0.4s var(--kid-ease-out) forwards;
    }
    /* Stagger entrance */
    .answer-card:nth-child(1) { animation-delay: 0.1s; }
    .answer-card:nth-child(2) { animation-delay: 0.18s; }
    .answer-card:nth-child(3) { animation-delay: 0.26s; }
    .answer-card:nth-child(4) { animation-delay: 0.34s; }
    .answer-card:nth-child(5) { animation-delay: 0.42s; }
    .answer-card:nth-child(6) { animation-delay: 0.50s; }

    /* Hover: gentle lift + purple glow */
    .answer-card:not(.locked):hover {
        transform: translateY(-4px);
        border-color: var(--kid-primary-light);
        box-shadow: 0 8px 24px rgba(124, 58, 237, 0.15);
    }
    /* Active press: satisfying squish */
    .answer-card:not(.locked):active {
        transform: scale(0.95);
        box-shadow: var(--kid-shadow-3d-pressed);
    }

    /* Selected (child tapped, not yet submitted) */
    .answer-card.selected {
        border-color: var(--kid-primary);
        background: #EDE9FE;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.2);
    }

    /* CORRECT: green glow + celebrate bounce + checkmark badge */
    .answer-card.correct {
        border-color: var(--kid-success);
        background: var(--kid-success-light);
        animation: celebrate-bounce 0.5s var(--kid-ease-spring);
    }
    .answer-card.correct .badge {
        position: absolute; top: -12px; right: -12px;
        width: 36px; height: 36px; border-radius: var(--kid-radius-full);
        background: var(--kid-success); color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; box-shadow: var(--kid-shadow-medium);
        animation: kid-pop-in 0.3s var(--kid-ease-spring) 0.2s backwards;
    }

    /* WRONG: fade to gray + gentle 4px shake (NEVER red!) */
    .answer-card.incorrect {
        border-color: var(--kid-border);
        background: #F9FAFB; opacity: 0.4;
        animation: gentle-shake 0.4s ease;
    }

    /* Locked (after submit) */
    .answer-card.locked { cursor: default; pointer-events: none; }

    /* Reveal (correct answer shown after 3 wrong tries) */
    .answer-card.reveal {
        border-color: var(--kid-success);
        background: var(--kid-success-light);
        animation: reveal-pulse 1s ease infinite;
    }

    /* The celebrate bounce: scale + slight rotate + settle */
    @keyframes celebrate-bounce {
        0%   { transform: scale(1); }
        25%  { transform: scale(1.12) rotate(-2deg); }
        50%  { transform: scale(0.96) rotate(1deg); }
        75%  { transform: scale(1.03) rotate(-1deg); }
        100% { transform: scale(1) rotate(0); }
    }
    /* The gentle shake: max 4px, 3 oscillations, ease (not violent) */
    @keyframes gentle-shake {
        0%, 100% { transform: translateX(0); }
        20%      { transform: translateX(-4px); }
        40%      { transform: translateX(4px); }
        60%      { transform: translateX(-2px); }
        80%      { transform: translateX(2px); }
    }
    /* Reveal pulse: draws attention without urgency */
    @keyframes reveal-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.4); }
        50%      { box-shadow: 0 0 0 10px rgba(34,197,94,0); }
    }

    .answer-card .card-emoji { font-size: 36px; line-height: 1; }
    .answer-card .card-text { font-size: var(--kid-text-answer); }

    /* ---- FOOTER: Next Button / Hint Prompt ---- */
    .quiz-footer {
        padding: var(--kid-space-4) var(--kid-space-5) var(--kid-space-6);
        display: flex; flex-direction: column; align-items: center; gap: var(--kid-space-3);
        min-height: 100px; justify-content: center;
    }
    .next-btn {
        padding: var(--kid-space-4) var(--kid-space-8);
        border-radius: var(--kid-radius-full); background: var(--kid-primary);
        color: white; font-family: var(--kid-font-heading);
        font-size: var(--kid-text-mission); font-weight: var(--kid-weight-black);
        border: none; cursor: pointer; box-shadow: var(--kid-shadow-3d);
        transition: all 0.15s var(--kid-ease-out); touch-action: manipulation;
        min-height: var(--kid-touch-min); display: none;
        align-items: center; gap: var(--kid-space-2);
        animation: kid-fade-slide-up 0.3s var(--kid-ease-out);
    }
    .next-btn.visible { display: flex; }
    .next-btn:hover { transform: translateY(-3px); box-shadow: 0 7px 0 rgba(0,0,0,0.15); }
    .next-btn:active { transform: translateY(1px); box-shadow: var(--kid-shadow-3d-pressed); }

    .footer-prompt {
        font-family: var(--kid-font-heading); font-weight: var(--kid-weight-medium);
        font-size: var(--kid-text-body); color: var(--kid-text-muted);
        animation: kid-fade-slide-up 0.3s var(--kid-ease-out);
    }

    /* ---- CELEBRATION OVERLAY ---- */
    .celebration-overlay {
        position: fixed; inset: 0; z-index: var(--kid-z-celebration);
        display: none; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.3); backdrop-filter: blur(4px);
    }
    .celebration-overlay.visible { display: flex; animation: fade-in 0.2s ease; }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }

    .celebration-box {
        background: white; border-radius: var(--kid-radius-xl);
        padding: var(--kid-space-7); text-align: center;
        box-shadow: var(--kid-shadow-popup); max-width: 320px;
        animation: kid-pop-in 0.4s var(--kid-ease-spring);
    }
    .celebration-emoji { font-size: 80px; line-height: 1; margin-bottom: var(--kid-space-3); }
    .celebration-title {
        font-family: var(--kid-font-heading); font-size: var(--kid-text-title);
        font-weight: var(--kid-weight-black); color: var(--kid-success-dark);
    }
    .celebration-subtitle {
        font-family: var(--kid-font-body); font-size: var(--kid-text-body);
        color: var(--kid-text-muted); margin-top: var(--kid-space-2);
    }

    /* Confetti pieces */
    .confetti-piece {
        position: absolute; width: 10px; height: 10px;
        animation: confetti-fall 2s ease-in forwards;
    }
    @keyframes confetti-fall {
        0%   { transform: translateY(-20px) rotate(0deg); opacity: 1; }
        100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
    }

    /* ---- IDLE HINT ---- */
    .idle-hint {
        position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
        background: var(--kid-primary); color: white; padding: var(--kid-space-3) var(--kid-space-5);
        border-radius: var(--kid-radius-full); font-family: var(--kid-font-heading);
        font-weight: var(--kid-weight-bold); font-size: var(--kid-text-body);
        box-shadow: var(--kid-shadow-popup); z-index: var(--kid-z-toast);
        display: none; animation: kid-pop-in 0.3s var(--kid-ease-spring);
    }
    .idle-hint.visible { display: block; }

    /* ---- DEV CONTROL PANEL ---- */
    .dev-panel {
        position: fixed; bottom: 0; right: 0; margin: var(--kid-space-4);
        background: rgba(255,255,255,0.95); border-radius: var(--kid-radius-md);
        padding: var(--kid-space-3); box-shadow: var(--kid-shadow-medium);
        font-family: monospace; font-size: 12px; z-index: 1000;
        max-width: 280px;
    }
    .dev-panel summary { cursor: pointer; font-weight: bold; }
    .dev-panel .dev-row { display: flex; align-items: center; gap: 8px; margin: 6px 0; }
    .dev-panel button {
        padding: 4px 8px; border-radius: 6px; border: 1px solid #ccc;
        background: white; cursor: pointer; font-size: 11px;
    }
    .dev-panel button.active { background: var(--kid-primary); color: white; }
    .dev-panel .event-log {
        max-height: 120px; overflow-y: auto; background: #1F2937; color: #4ADE80;
        padding: 8px; border-radius: 6px; font-size: 10px; margin-top: 8px;
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            animation-duration: 0.01ms !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
@endpush

@section('kid-content')
<div class="quiz-stage" x-data="qt01Polished()" x-init="init()" x-cloak>

    {{-- HEADER: Progress + Stars + Exit --}}
    <div class="quiz-header">
        <button class="exit-btn" @click="exitQuiz()" aria-label="Exit quiz">🗺️</button>
        <div class="progress-wrap">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" :style="`width: ${progressPercent}%`"></div>
            </div>
            <div class="progress-label" x-text="`Question ${currentIndex + 1} of ${questions.length}`"></div>
        </div>
        <div class="star-pill" :class="{ pulse: starPulse }" @click="starPulse = false">
            <span class="star-icon">⭐</span>
            <span x-text="stars"></span>
        </div>
    </div>

    {{-- LEO ZONE --}}
    <div class="leo-zone" x-show="leoMessage">
        <div class="leo-mascot" :class="{ celebrating: leoCelebrating }">🦁</div>
        <div class="leo-bubble" :class="{ encouraging: leoEncouraging }" x-text="leoMessage"></div>
    </div>

    {{-- QUESTION --}}
    <div class="question-zone" :key="'q-' + currentIndex">
        <div class="question-prompt" x-text="currentQuestion.prompt"></div>
    </div>

    {{-- ANSWER GRID --}}
    <div class="answer-zone" :key="'a-' + currentIndex">
        <div class="answer-grid" :data-count="currentQuestion.options.length">
            <template x-for="(option, i) in currentQuestion.options" :key="i">
                <div class="answer-card"
                     :class="getCardClass(i, option.is_correct)"
                     :style="`animation-delay: ${0.1 + i * 0.08}s`"
                     @click="selectOption(i)"
                     :aria-label="`Answer option: ${option.text}`"
                     role="button"
                     tabindex="0"
                     @keydown.enter="selectOption(i)"
                     @keydown.space.prevent="selectOption(i)">
                    <template x-if="option.emoji">
                        <span class="card-emoji" x-text="option.emoji"></span>
                    </template>
                    <span class="card-text" x-text="option.text"></span>
                    <template x-if="answered && selectedIndex === i && option.is_correct">
                        <span class="badge">✅</span>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="quiz-footer">
        <template x-if="!answered && !showHint">
            <div class="footer-prompt">👆 Tap the right answer!</div>
        </template>
        <template x-if="showHint && !answered">
            <div class="footer-prompt" style="color: var(--kid-encourage-dark);">
                🤔 <span x-text="hintText"></span>
            </div>
        </template>
        <template x-if="answered">
            <button class="next-btn visible" @click="nextQuestion()">
                <span x-text="currentIndex + 1 >= questions.length ? '🎉 See My Stars!' : 'Next Question →'"></span>
            </button>
        </template>
    </div>

    {{-- CELEBRATION OVERLAY --}}
    <div class="celebration-overlay" :class="{ visible: celebrating }" @click="dismissCelebration()">
        <div class="celebration-box" @click.stop>
            <div class="celebration-emoji" x-text="celebrationData.emoji"></div>
            <div class="celebration-title" x-text="celebrationData.title"></div>
            <div class="celebration-subtitle" x-text="celebrationData.subtitle"></div>
            <template x-if="celebrationData.confetti">
                <div class="mt-4" style="font-size: 24px;">
                    <template x-for="n in celebrationData.confetti" :key="n">
                        <span>🎉</span>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- IDLE HINT --}}
    <div class="idle-hint" :class="{ visible: showIdleHint }">
        🦁 <span x-text="idleHintText"></span>
    </div>

    {{-- DEV CONTROL PANEL --}}
    <details class="dev-panel">
        <summary>🔧 Dev Panel</summary>
        <div class="dev-row">
            <button @click="restartQuiz()">🔄 Restart</button>
            <button @click="skipQuestion()">⏭️ Skip</button>
        </div>
        <div class="dev-row">
            <label><input type="checkbox" x-model="soundEnabled" @change="toggleSound()"> 🔊 Sound</label>
        </div>
        <div class="dev-row">
            <span>Stars: <strong x-text="stars"></strong></span>
            <span>|</span>
            <span>Streak: <strong x-text="streak"></strong></span>
        </div>
        <div class="event-log" id="event-log"></div>
    </details>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/kid/quiz-event-bus.js') }}"></script>
<script src="{{ asset('js/kid/quiz-sound-layer.js') }}"></script>
<script src="{{ asset('js/kid/quiz-reward-layer.js') }}"></script>
<script>
function qt01Polished() {
    return {
        // ---- QUESTIONS (5 polished examples) ----
        questions: [
            {
                prompt: "Which one is the letter A?",
                options: [
                    { text: "A", emoji: "🅰️", is_correct: true },
                    { text: "B", emoji: "🐝", is_correct: false },
                    { text: "C", emoji: "🐱", is_correct: false },
                    { text: "D", emoji: "🐶", is_correct: false },
                ]
            },
            {
                prompt: "What color is the sky?",
                options: [
                    { text: "Blue", emoji: "🔵", is_correct: true },
                    { text: "Red", emoji: "🔴", is_correct: false },
                    { text: "Green", emoji: "🟢", is_correct: false },
                ]
            },
            {
                prompt: "How many fingers on one hand?",
                options: [
                    { text: "3", is_correct: false },
                    { text: "5", is_correct: true },
                    { text: "7", is_correct: false },
                ]
            },
            {
                prompt: "Which animal says 'Moo'?",
                options: [
                    { text: "Dog", emoji: "🐕", is_correct: false },
                    { text: "Cat", emoji: "🐈", is_correct: false },
                    { text: "Cow", emoji: "🐄", is_correct: true },
                    { text: "Duck", emoji: "🦆", is_correct: false },
                ]
            },
            {
                prompt: "What is 2 + 2?",
                options: [
                    { text: "3", is_correct: false },
                    { text: "4", is_correct: true },
                    { text: "5", is_correct: false },
                ]
            },
        ],

        // ---- STATE ----
        currentIndex: 0,
        currentQuestion: null,
        selectedIndex: null,
        answered: false,
        wrongIndices: [],
        wrongAttempts: 0,
        stars: 0,
        streak: 0,
        showHint: false,
        hintText: '',
        leoMessage: '',
        leoEncouraging: false,
        leoCelebrating: false,
        starPulse: false,
        celebrating: false,
        celebrationData: {},
        soundEnabled: true,
        showIdleHint: false,
        idleHintText: '',
        lastInteraction: Date.now(),
        idleTimer: null,

        // Encouraging messages (per Guidelines Section 8)
        encouragementMsgs: [
            "Almost! Try again! 💪",
            "Good try! You can do it! 🌟",
            "Look carefully — you've got this! 👀",
        ],

        get progressPercent() {
            return ((this.currentIndex) / this.questions.length) * 100;
        },

        init() {
            // Wire up the layers
            this.currentQuestion = this.questions[0];
            this.leoMessage = "Hi! I'm Leo! Let's play! 🦁";

            // Connect layers
            window.KidSoundLayer.connect();
            window.KidRewardLayer.connect();
            window.KidRewardLayer.reset();

            // Subscribe to events for UI updates
            const E = window.KidQuizEvents.EVENTS;
            window.KidQuizEvents.on(E.STAR_EARNED, (data) => {
                this.stars = data.total;
                this.starPulse = true;
                setTimeout(() => this.starPulse = false, 600);
            });
            window.KidQuizEvents.on(E.STREAK_UPDATED, (data) => this.streak = data.streak);
            window.KidQuizEvents.on(E.CELEBRATION_TRIGGERED, (data) => this.handleCelebration(data));
            window.KidQuizEvents.on(E.HINT_SHOWN, (data) => {
                this.showHint = true;
                this.hintText = data.message;
            });

            // Dev panel event log
            window.KidQuizEvents.on('*', (data) => this.logEvent(data));

            // Emit quiz started
            window.KidQuizEvents.emit(E.QUIZ_STARTED, { totalQuestions: this.questions.length });
            setTimeout(() => this.startQuestion(), 1000);

            // Start idle timer
            this.resetIdleTimer();
        },

        startQuestion() {
            this.currentQuestion = this.questions[this.currentIndex];
            this.selectedIndex = null;
            this.answered = false;
            this.wrongIndices = [];
            this.wrongAttempts = 0;
            this.showHint = false;
            this.leoEncouraging = false;
            this.leoCelebrating = false;
            this.leoMessage = this.getEncouragement();

            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUESTION_STARTED, {
                index: this.currentIndex,
                prompt: this.currentQuestion.prompt,
            });
            this.resetIdleTimer();
        },

        getEncouragement() {
            const msgs = [
                "You can do it! 🌟",
                "Take your time! 😊",
                "I believe in you! 💪",
                "Let's go! 🚀",
            ];
            return msgs[Math.floor(Math.random() * msgs.length)];
        },

        selectOption(index) {
            this.resetIdleTimer();
            if (this.answered) return;
            if (this.wrongIndices.includes(index)) return;

            const option = this.currentQuestion.options[index];

            // Emit selected event
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_SELECTED, {
                index, correct: option.is_correct,
            });

            if (option.is_correct) {
                this.handleCorrect(index);
            } else {
                this.handleWrong(index);
            }
        },

        handleCorrect(index) {
            this.selectedIndex = index;
            this.answered = true;
            this.leoCelebrating = true;
            this.leoMessage = this.wrongAttempts === 0 ? "Perfect! You're a star! ⭐" : "You got it! Well done! 🎉";
            this.leoEncouraging = false;

            // Emit correct event (Reward layer handles stars + streaks)
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                index, firstTry: this.wrongAttempts === 0,
            });

            setTimeout(() => { this.leoCelebrating = false; }, 500);
        },

        handleWrong(index) {
            this.wrongAttempts++;
            this.wrongIndices.push(index);
            this.leoEncouraging = true;
            this.leoCelebrating = false;
            this.leoMessage = this.encouragementMsgs[Math.min(this.wrongAttempts - 1, 2)];

            // Emit incorrect + hint events
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_INCORRECT, {
                index, attempts: this.wrongAttempts,
            });

            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.HINT_SHOWN, {
                message: this.encouragementMsgs[Math.min(this.wrongAttempts - 1, 2)],
                attempt: this.wrongAttempts,
            });

            // After 3 wrong tries, reveal the answer
            if (this.wrongAttempts >= 3) {
                setTimeout(() => this.revealAnswer(), 800);
            }
        },

        revealAnswer() {
            this.answered = true;
            const correctIdx = this.currentQuestion.options.findIndex(o => o.is_correct);
            this.selectedIndex = correctIdx;
            this.leoMessage = "Here's the answer! Let's keep going! 💪";
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_REVEALED, {
                correctIndex: correctIdx,
            });
        },

        getCardClass(index, isCorrect) {
            if (this.answered && this.selectedIndex === index && isCorrect) return 'correct locked';
            if (this.wrongIndices.includes(index)) return 'incorrect locked';
            if (this.wrongAttempts >= 3 && isCorrect && !this.answered) return 'reveal locked';
            if (this.answered || this.wrongAttempts >= 3) return 'locked';
            if (this.selectedIndex === index) return 'selected';
            return '';
        },

        nextQuestion() {
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUESTION_COMPLETED, {
                index: this.currentIndex, attempts: this.wrongAttempts,
            });
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.NEXT_QUESTION);

            this.currentIndex++;
            if (this.currentIndex >= this.questions.length) {
                this.finishQuiz();
            } else {
                this.startQuestion();
            }
        },

        finishQuiz() {
            window.KidRewardLayer.completeQuiz();
            this.leoMessage = "You finished the quiz! Amazing! 🏆";
        },

        handleCelebration(data) {
            const levels = {
                mini: { emoji: '🎉', title: 'Yes!', subtitle: 'Great job!', confetti: 0 },
                small: { emoji: '🌟', title: 'Awesome Streak!', subtitle: `${this.streak} in a row!`, confetti: 3 },
                medium: { emoji: '🏆', title: 'Quiz Complete!', subtitle: `You earned ${this.stars} stars!`, confetti: 8 },
            };
            this.celebrationData = levels[data.level] || levels.mini;
            this.celebrating = true;
            // Auto-dismiss mini/small after a moment
            if (data.level !== 'medium') {
                setTimeout(() => this.dismissCelebration(), 1800);
            }
        },

        dismissCelebration() { this.celebrating = false; },

        exitQuiz() {
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.EXIT_REQUESTED);
            if (confirm('Leave the quiz? Your progress will be saved.')) {
                window.history.back();
            }
        },

        // ---- IDLE BEHAVIOR (per Guidelines Section 6) ----
        resetIdleTimer() {
            this.lastInteraction = Date.now();
            this.showIdleHint = false;
            clearTimeout(this.idleTimer);
            this.idleTimer = setTimeout(() => this.checkIdle(), 5000); // Check every 5s
        },

        checkIdle() {
            if (this.answered) return;
            const elapsed = (Date.now() - this.lastInteraction) / 1000;
            if (elapsed > 15 && elapsed < 20) {
                this.idleHintText = "Tap an answer! 👆";
                this.showIdleHint = true;
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.IDLE_HINT, { elapsed, level: 'gentle' });
            } else if (elapsed >= 30) {
                this.idleHintText = "Need help? Tap any picture! 💛";
                this.showIdleHint = true;
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.IDLE_RESCUE, { elapsed, level: 'rescue' });
            }
            this.idleTimer = setTimeout(() => this.checkIdle(), 5000);
        },

        // ---- DEV PANEL ----
        restartQuiz() {
            this.currentIndex = 0;
            this.stars = 0;
            this.streak = 0;
            window.KidRewardLayer.reset();
            this.startQuestion();
        },

        skipQuestion() { this.nextQuestion(); },

        toggleSound() {
            window.KidSoundLayer.setMuted(!this.soundEnabled);
            if (this.soundEnabled) window.KidSoundLayer.init();
        },

        logEvent(data) {
            const log = document.getElementById('event-log');
            if (log) {
                const time = new Date(data.timestamp).toLocaleTimeString();
                log.innerHTML = `<div>[${time}] ${data.event}</div>` + log.innerHTML;
                log.scrollTop = 0;
            }
        },
    };
}
</script>
@endpush