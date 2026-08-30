/**
 * ============================================================
 * KID UI — REWARD LAYER
 * Manages stars, streaks, and celebrations.
 * Knows NOTHING about quizzes — only listens to events.
 * ============================================================
 */
window.KidRewardLayer = (function () {
    let state = {
        stars: 0,
        streak: 0,
        correctCount: 0,
        totalCount: 0,
        perfectFirstTry: true,
    };

    // ---- CELEBRATION LEVELS (per Interaction Guidelines Section 7) ----
    // Mini: single correct answer
    // Small: 3 correct in a row
    // Medium: quiz complete
    // Large: world complete
    const CELEBRATION_LEVELS = {
        MINI: 'mini',     // card bounce + chime (1s)
        SMALL: 'small',   // Leo thumbs up + sparkle (2s)
        MEDIUM: 'medium', // confetti + Leo dance (3s)
        LARGE: 'large',   // full celebration page (5s+)
    };

    function reset() {
        state = {
            stars: 0,
            streak: 0,
            correctCount: 0,
            totalCount: 0,
            perfectFirstTry: true,
        };
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.PROGRESS_UPDATED, getState());
    }

    function addStar() {
        state.stars++;
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.STAR_EARNED, { total: state.stars });
    }

    function recordCorrect(firstTry = true) {
        state.correctCount++;
        state.streak++;

        // Star only on first try (per Guidelines: stars feel earned)
        if (firstTry) {
            addStar();
        } else {
            state.perfectFirstTry = false;
        }

        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.STREAK_UPDATED, { streak: state.streak });

        // Only trigger visual celebration on streaks of 3+
        if (state.streak >= 3 && state.streak % 3 === 0) {
            triggerCelebration(CELEBRATION_LEVELS.SMALL);
        }
    }

    function recordIncorrect() {
        state.streak = 0;
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.STREAK_UPDATED, { streak: 0 });
    }

    function recordQuestionCompleted() {
        state.totalCount++;
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.PROGRESS_UPDATED, getState());
    }

    function triggerCelebration(level = CELEBRATION_LEVELS.MINI) {
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.CELEBRATION_TRIGGERED, { level });
    }

    function completeQuiz() {
        // Bonus stars for perfect quiz (all first-try)
        if (state.perfectFirstTry) {
            state.stars += 3;
            window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.STAR_EARNED, {
                total: state.stars,
                bonus: 3,
                reason: 'perfect_quiz',
            });
        }
        triggerCelebration(CELEBRATION_LEVELS.MEDIUM);
        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUIZ_COMPLETED, getState());
    }

    function getState() {
        return { ...state };
    }

    let isConnected = false;

    function connect() {
        if (isConnected) return;
        isConnected = true;
        
        const E = window.KidQuizEvents.EVENTS;
        window.KidQuizEvents.on(E.ANSWER_CORRECT, (data) => recordCorrect(data.firstTry));
        window.KidQuizEvents.on(E.ANSWER_INCORRECT, () => recordIncorrect());
        window.KidQuizEvents.on(E.QUESTION_COMPLETED, () => recordQuestionCompleted());
    }

    return {
        reset, recordCorrect, recordIncorrect, recordQuestionCompleted,
        completeQuiz, triggerCelebration, getState, connect,
        CELEBRATION_LEVELS,
    };
})();