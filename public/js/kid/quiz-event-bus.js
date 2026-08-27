/**
 * ============================================================
 * KID UI — QUIZ EVENT BUS
 * The central nervous system of the quiz engine.
 * Every meaningful action emits an event here.
 *
 * Future AI/adaptive learning will subscribe to this bus
 * without touching the quiz engine itself.
 * ============================================================
 */
window.KidQuizEvents = (function () {
    const listeners = {};

    /**
     * Subscribe to an event.
     * @param {string} event
     * @param {Function} callback
     */
    function on(event, callback) {
        if (!listeners[event]) listeners[event] = [];
        listeners[event].push(callback);
    }

    /**
     * Emit an event with data.
     * @param {string} event
     * @param {object} data
     */
    function emit(event, data = {}) {
        const payload = {
            event,
            timestamp: Date.now(),
            ...data,
        };
        // Console log in dev mode for debugging the event stream
        if (window.location.pathname.includes('/dev/')) {
            // eslint-disable-next-line no-console
            console.log(`[KidQuizEvent] ${event}`, payload);
        }
        if (listeners[event]) {
            listeners[event].forEach((cb) => cb(payload));
        }
        // Also emit to a catch-all for analytics
        if (listeners['*']) {
            listeners['*'].forEach((cb) => cb(payload));
        }
    }

    /**
     * Remove a listener.
     */
    function off(event, callback) {
        if (!listeners[event]) return;
        listeners[event] = listeners[event].filter((cb) => cb !== callback);
    }

    // ---- STANDARD EVENT NAMES ----
    // These are the vocabulary of the quiz engine.
    // Any layer can listen for any of these.
    const EVENTS = {
        // Lifecycle
        QUIZ_STARTED: 'quiz:started',
        QUIZ_COMPLETED: 'quiz:completed',
        QUESTION_STARTED: 'question:started',
        QUESTION_COMPLETED: 'question:completed',

        // Interaction
        ANSWER_SELECTED: 'answer:selected',     // Child tapped an option
        ANSWER_CHANGED: 'answer:changed',       // Child changed selection (multi-select types)
        ANSWER_SUBMITTED: 'answer:submitted',   // Child confirmed answer

        // Feedback
        ANSWER_CORRECT: 'answer:correct',
        ANSWER_INCORRECT: 'answer:incorrect',
        HINT_SHOWN: 'hint:shown',
        RETRY_USED: 'retry:used',
        ANSWER_REVEALED: 'answer:revealed',     // After 3 wrong tries

        // Progress
        PROGRESS_UPDATED: 'progress:updated',
        STREAK_UPDATED: 'streak:updated',

        // Rewards
        STAR_EARNED: 'reward:star:earned',
        BADGE_EARNED: 'reward:badge:earned',
        CELEBRATION_TRIGGERED: 'celebration:triggered',

        // Navigation
        NEXT_QUESTION: 'nav:next',
        EXIT_REQUESTED: 'nav:exit',

        // Idle
        IDLE_HINT: 'idle:hint',
        IDLE_RESCUE: 'idle:rescue',

        // Audio
        AUDIO_PLAY: 'audio:play',
        AUDIO_STOP: 'audio:stop',
    };

    return { on, off, emit, EVENTS };
})();