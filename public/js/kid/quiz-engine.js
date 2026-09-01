function quizEngine(config) {
    return {
        // ---- STATE ----
        questions: config.questions,
        submitUrl: config.submitUrl,
        exitUrl: config.exitUrl,
        initialized: false,
        currentIndex: 0,
        currentQuestion: null,
        selectedIndex: null,
        answered: false,
        wrongIndices: [],
        wrongAttempts: 0,
        score: 0,
        starsEarned: 0,
        totalStars: config.childStars,
        starPulse: false,
        quizComplete: false,
        resultMessage: '',

        // Leo
        leoMessage: '',
        leoCelebrating: false,

        // Hint
        showHint: false,

        // Matching (QT-03)
        matchSelectedSide: null,
        matchSelectedIndex: null,
        matchedPairs: [],
        matchLeftItems: [],
        matchRightItems: [],
        matchWrongPair: null,

        // Sequence (QT-05)
        seqCards: [],      // shuffled cards [{text, correctIndex}]
        seqSlots: [],      // array of indices into seqCards, or null
        seqSelectedCard: null,
        seqAnswered: false,

        // Count Objects
        countObjectsTapped: [], // tracks which objects are marked [false, false, ...]

        // Drag & Sort (QT-04)
        sortChips: [],         // [{text, correctBucket, bucket}]
        sortCategories: [],    // strings e.g. ["Farm", "Wild"]
        sortSelectedChip: null,
        sortSelectedBucket: null,
        sortAnswered: false,

        // Listen & Choose (QT-06)
        isSpeaking: false,
        speechUtterance: null,

        // Memory Match (QT-11)
        memoryCards: [],          // [{text, pairKey, matched, flipped}]
        memoryFlippedIndices: [], // indices of currently face-up (non-matched) cards
        memoryMatchedPairs: 0,
        memoryTotalPairs: 0,
        memoryChecking: false,    // prevent clicks during match-check delay
        memoryAnswered: false,

        // Speak & Repeat (QT-07)
        speakListening: false,    // mic is "recording"
        speakAttempts: 0,         // how many times child tapped mic
        speakCompleted: false,    // question done
        speakStatus: '',          // status message shown to child
        speakDots: [false, false, false], // attempt indicators
        speakTimer: null,         // fallback timer if no speech API
        speakRecognition: null,   // SpeechRecognition instance (real mic)
        speakSupported: false,    // whether browser supports Web Speech API
        speakTargetWord: '',      // the word the child must say
        speakLastHeard: '',       // last transcript received

        // Tracing (QT-12) — practice only (no scoring)
        tracingStrokes: 0,        // how many strokes the child drew
        tracingStatus: '',        // status message
        tracingCompleted: false,  // question done
        tracingCanvasEl: null,    // canvas DOM reference
        tracingDemoPlaying: false, // animated demo is playing

        // Celebration
        celebrating: false,
        celebrationData: {},

        // Idle
        showIdleHint: false,
        idleHintText: '',
        lastInteraction: Date.now(),
        idleTimer: null,

        // Tracking
        startTime: Date.now(),
        timeSpent: 0,
        answerLog: [],

        // ---- COMPUTED ----
        get progressPercent() {
            if (this.quizComplete) return 100;
            return (this.currentIndex / this.questions.length) * 100;
        },

        // ---- INIT ----
        init() {
            if (this.questions.length === 0) {
                this.leoMessage = "No questions in this quiz yet!";
                this.initialized = true;
                return;
            }

            this.currentQuestion = this.questions[0];
            this.leoMessage = "Hi! I'm Leo! Let's play! 🦁";
            this.initialized = true;

            // Connect layers
            if (window.KidSoundLayer) window.KidSoundLayer.connect();
            if (window.KidRewardLayer) {
                window.KidRewardLayer.connect();
                window.KidRewardLayer.reset();
            }

            // Subscribe to events
            if (window.KidQuizEvents) {
                const E = window.KidQuizEvents.EVENTS;
                window.KidQuizEvents.on(E.STAR_EARNED, (data) => {
                    this.totalStars = config.childStars + data.total;
                    this.starPulse = true;
                    setTimeout(() => this.starPulse = false, 600);
                });
                window.KidQuizEvents.on(E.CELEBRATION_TRIGGERED, (data) => this.handleCelebration(data));
                window.KidQuizEvents.emit(E.QUIZ_STARTED, { totalQuestions: this.questions.length });
            }

            setTimeout(() => this.startQuestion(), 800);
            this.resetIdleTimer();
        },

        // ---- QUESTION FLOW ----
        startQuestion() {
            this.currentQuestion = this.questions[this.currentIndex];
            this.selectedIndex = null;
            this.answered = false;
            this.wrongIndices = [];
            this.wrongAttempts = 0;
            this.showIdleHint = false;
            this.leoCelebrating = false;
            this.leoMessage = this.getEncouragement();

            // Initialize matching if this is a matching question
            if (this.currentQuestion.type === 'matching') {
                this.initMatching();
            }

            // Initialize Count Objects state
            if (this.currentQuestion.type === 'count-objects' || this.currentQuestion.type === 'count_objects') {
                const count = this.currentQuestion.scoring_config?.count || 5;
                this.countObjectsTapped = Array(count).fill(false);
            }

            // Initialize sequence if this is a sequence question
            if (this.currentQuestion.type === 'drag_sequence') {
                this.initSequence();
            }

            // Initialize sorting if this is a drag_sort question
            if (this.currentQuestion.type === 'drag_sort') {
                this.initSorting();
            }

            // Initialize memory match if this is a memory_match question
            if (this.currentQuestion.type === 'memory_match') {
                this.initMemoryMatch();
            }

            // Initialize speak & repeat if this is a speak_repeat question
            if (this.currentQuestion.type === 'speak_repeat') {
                this.initSpeakRecognition();
            }

            // Reset tracing state if this is a tracing question
            if (this.currentQuestion.type === 'tracing') {
                this.tracingStrokes = 0;
                this.tracingStatus = '✏️ Trace with your finger!';
                this.tracingCompleted = false;
                this.tracingDemoPlaying = false;
                
                // Redraw the canvas for the new letter/number if it already exists
                if (this.tracingCanvasEl) {
                    this.$nextTick(() => {
                        this.drawTracingGuide(this.tracingCanvasEl.getContext('2d'), 300);
                    });
                }
            }

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUESTION_STARTED, {
                    index: this.currentIndex,
                    prompt: this.currentQuestion.prompt,
                });
            }

            // 🔊 AUTO-PLAY question audio (narration or prompt_audio_url)
            // For listen_choose / speak_repeat, their own init methods handle audio.
            const skipAutoPlay = ['listen_choose', 'speak_repeat'].includes(this.currentQuestion.type);
            if (!skipAutoPlay) {
                setTimeout(() => this.playQuestionAudio(), 600);
            }

            this.resetIdleTimer();
        },

        // 🔊 Play question narration / prompt audio
        playQuestionAudio() {
            // Cancel any ongoing TTS speech
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            
            if (this.currentQuestion.audio) {
                const audio = new Audio(this.currentQuestion.audio);
                audio.play().catch(e => console.log('Question audio playback deferred:', e));
            } else if (this.currentQuestion.narration_text) {
                this.speakText(this.currentQuestion.narration_text);
            } else if (this.currentQuestion.prompt) {
                this.speakText(this.currentQuestion.prompt);
            }
        },

        // 🔊 Play an option's audio (tap-to-play)
        // Called when a kid taps an answer card that has audio attached.
        // Returns true if audio was played (so the caller can decide whether
        // to also select the option or wait for the audio to finish).
        playOptionAudio(option) {
            if (!option.audio) return false;
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            const audio = new Audio(option.audio);
            audio.play().catch(e => console.log('Option audio playback deferred:', e));
            return true;
        },

        getEncouragement() {
            const msgs = ["You can do it! 🌟", "Take your time! 😊", "I believe in you! 💪", "Let's go! 🚀"];
            return msgs[Math.floor(Math.random() * msgs.length)];
        },

        // ---- ANSWER SELECTION ----
        selectOption(index) {
            this.resetIdleTimer();
            if (this.answered) return;
            if (this.wrongIndices.includes(index)) return;

            const option = this.currentQuestion.options[index];

            // 🔊 Only play option audio for listen_choose type to prevent overlapping correct/wrong sound effects
            if (option.audio && this.currentQuestion.type === 'listen_choose') {
                this.playOptionAudio(option);
            }

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_SELECTED, {
                    index, correct: option.is_correct,
                });
            }

            if (option.is_correct) {
                this.handleCorrect(index);
            } else {
                this.handleWrong(index);
            }
        },

        selectFlashcard(correct) {
            this.resetIdleTimer();
            if (this.answered) return;

            this.selectedIndex = correct ? 1 : 0;
            this.answered = true;
            this.leoCelebrating = correct;
            this.leoMessage = correct ? "Great job! ⭐" : "That's okay! Keep practicing! 💪";

            if (correct) {
                this.score++;
                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                        index: 1, firstTry: true,
                    });
                }
            }

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                selected: correct ? 'yes' : 'no',
                correct: correct,
            });

            setTimeout(() => { this.leoCelebrating = false; }, 500);

            // ✨ AUTO-ADVANCE flashcard after 1.8s
            setTimeout(() => { this.nextQuestion(); }, 1800);
        },

        handleCorrect(index) {
            this.selectedIndex = index;
            this.answered = true;
            this.leoCelebrating = true;
            this.leoMessage = this.wrongAttempts === 0 ? "Perfect! You're a star! ⭐" : "You got it! Well done! 🎉";

            this.score++;

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                    index, firstTry: this.wrongAttempts === 0,
                });
            }

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                option_id: this.currentQuestion.options[index].id,
                correct: true,
                attempts: this.wrongAttempts + 1,
            });

            this.spawnConfetti(15);
            setTimeout(() => { this.leoCelebrating = false; }, 500);

            // ✨ AUTO-ADVANCE: After showing the correct answer for 1.8s, go to next question
            setTimeout(() => { this.nextQuestion(); }, 1800);
        },

        handleWrong(index) {
            this.wrongAttempts++;
            this.wrongIndices.push(index);
            this.showHint = true;
            this.leoMessage = this.getEncouragementMsg();

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_INCORRECT, {
                    index, attempts: this.wrongAttempts,
                });
            }

            if (this.wrongAttempts >= 3) {
                setTimeout(() => this.revealAnswer(), 800);
            }
        },

        getEncouragementMsg() {
            const msgs = ["Almost! Try again! 💪", "Good try! You can do it! 🌟", "Look carefully — you've got this! 👀"];
            return msgs[Math.min(this.wrongAttempts - 1, 2)];
        },

        revealAnswer() {
            this.answered = true;
            const correctIdx = this.currentQuestion.options.findIndex(o => o.is_correct);
            this.selectedIndex = correctIdx;
            this.leoMessage = "Here's the answer! Let's keep going! 💪";

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_REVEALED, {
                    correctIndex: correctIdx,
                });
            }

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                option_id: this.currentQuestion.options[correctIdx].id,
                correct: false,
                revealed: true,
                attempts: this.wrongAttempts,
            });

            // ✨ AUTO-ADVANCE after reveal — give 3s to study the answer
            setTimeout(() => { this.nextQuestion(); }, 3000);
        },

        getCardClass(index, isCorrect) {
            if (this.answered && this.selectedIndex === index && isCorrect) return 'correct locked';
            if (this.wrongIndices.includes(index)) return 'incorrect locked';
            if (this.wrongAttempts >= 3 && isCorrect && !this.answered) return 'reveal locked';
            if (this.answered || this.wrongAttempts >= 3) return 'locked';
            if (this.selectedIndex === index) return 'selected';
            return '';
        },

        // ---- PATTERN HELPER (QT-10) ----
        getCorrectAnswerText() {
            const correct = this.currentQuestion.options.find(o => o.is_correct);
            return correct ? (correct.text || '') : '?';
        },
        getCorrectAnswerImage() {
            const correct = this.currentQuestion.options.find(o => o.is_correct);
            return correct ? correct.image : null;
        },

        // ---- LISTEN & CHOOSE — AUDIO PLAYBACK (QT-06) ----
        // Uses Web Speech API (browser built-in text-to-speech)
        // No audio files needed! Teacher just types the word in metadata.audio_text
        playAudio() {
            this.resetIdleTimer();
            if (this.isSpeaking) {
                // If already playing, stop and restart
                if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            }

            const text = this.currentQuestion.metadata?.audio_text || 'Listen carefully!';
            // Prefer question-level audio (narration/prompt_audio_url), then metadata audio_url
            const audioUrl = this.currentQuestion.audio || this.currentQuestion.metadata?.audio_url || null;

            // If we have an actual audio file, use HTML5 audio
            if (audioUrl) {
                const audio = new Audio(audioUrl);
                this.isSpeaking = true;
                audio.onended = () => { this.isSpeaking = false; };
                audio.onerror = () => {
                    this.isSpeaking = false;
                    // Fallback to speech synthesis
                    this.speakText(text);
                };
                audio.play();
                return;
            }

            // Otherwise use Web Speech API (text-to-speech)
            this.speakText(text);
        },

        speakText(text) {
            if (!('speechSynthesis' in window)) {
                // Browser doesn't support speech — just show the text as a hint
                this.leoMessage = '🔊 ' + text;
                return;
            }

            window.speechSynthesis.cancel(); // clear any pending speech
            const utterance = new SpeechSynthesisUtterance(text);
            
            const isMale = (config.voiceProfile === 'male' || config.voiceProfile === 'david');
            utterance.rate = 0.8;   // slower for kids
            utterance.pitch = isMale ? 0.9 : 1.3;  // lower pitch for male
            utterance.volume = 1.0;

            // Try to pick a friendly English voice matching the profile
            const voices = window.speechSynthesis.getVoices();
            let preferredVoice;
            if (isMale) {
                preferredVoice = voices.find(v => v.lang.startsWith('en') && /male|david|daniel|google uk english male/i.test(v.name));
            } else {
                preferredVoice = voices.find(v => v.lang.startsWith('en') && /female|samantha|zira|google uk english female/i.test(v.name));
            }
            if (!preferredVoice) {
                preferredVoice = voices.find(v => /^en/i.test(v.lang));
            }
            if (preferredVoice) utterance.voice = preferredVoice;

            utterance.onstart = () => { this.isSpeaking = true; };
            utterance.onend = () => { this.isSpeaking = false; };
            utterance.onerror = () => { this.isSpeaking = false; };

            this.speechUtterance = utterance;
            window.speechSynthesis.speak(utterance);
        },

        // ---- NAVIGATION ----
        nextQuestion() {
            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUESTION_COMPLETED, {
                    index: this.currentIndex, attempts: this.wrongAttempts,
                });
            }

            this.currentIndex++;
            if (this.currentIndex >= this.questions.length) {
                this.finishQuiz();
            } else {
                this.startQuestion();
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        finishQuiz() {
            console.log('%c 🎉 [QUIZ-ENGINE] Quiz Finished! Preparing submission...', 'background: #3b82f6; color: white; font-size: 14px; font-weight: bold; padding: 4px 8px; border-radius: 4px;');
            this.quizComplete = true;
            this.timeSpent = Math.round((Date.now() - this.startTime) / 1000);
            this.calculateStars();
            this.setResultMessage();

            if (window.KidRewardLayer) window.KidRewardLayer.completeQuiz();
            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.QUIZ_COMPLETED, {
                    score: this.score, total: this.questions.length, stars: this.starsEarned,
                });
            }

            this.spawnConfetti(40);
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Automatically submit form to backend to persist score, stars, and coins in PostgreSQL
            setTimeout(() => {
                this.submitQuiz();
            }, 1200);
        },

        calculateStars() {
            const pct = (this.score / this.questions.length) * 100;
            if (pct >= 90) this.starsEarned = 3;
            else if (pct >= 60) this.starsEarned = 2;
            else if (pct >= 30) this.starsEarned = 1;
            else this.starsEarned = 0;
        },

        setResultMessage() {
            const pct = (this.score / this.questions.length) * 100;
            if (pct === 100) this.resultMessage = "🌟 Perfect Score! You're a superstar!";
            else if (pct >= 80) this.resultMessage = '🎉 Amazing! Almost perfect!';
            else if (pct >= 60) this.resultMessage = '👍 Great job! Keep practicing!';
            else if (pct >= 40) this.resultMessage = "💪 Good try! You're learning!";
            else this.resultMessage = '🌱 Every expert was once a beginner!';
        },

        // ---- CELEBRATION ----
        handleCelebration(data) {
            const levels = {
                mini: { emoji: '🎉', title: 'Yes!', subtitle: 'Great job!' },
                small: { emoji: '🌟', title: 'Awesome!', subtitle: 'Keep it up!' },
                medium: { emoji: '🏆', title: 'Amazing!', subtitle: `You earned ${this.starsEarned} stars!` },
            };
            this.celebrationData = levels[data.level] || levels.mini;
            this.celebrating = true;
            if (data.level !== 'medium') {
                setTimeout(() => this.dismissCelebration(), 1800);
            }
        },

        dismissCelebration() { this.celebrating = false; },

        // ---- IDLE ----
        resetIdleTimer() {
            this.lastInteraction = Date.now();
            this.showIdleHint = false;
            clearTimeout(this.idleTimer);
            this.idleTimer = setTimeout(() => this.checkIdle(), 5000);
        },

        checkIdle() {
            if (this.answered || this.quizComplete) return;
            const elapsed = (Date.now() - this.lastInteraction) / 1000;
            if (elapsed > 15 && elapsed < 30) {
                this.idleHintText = "Tap an answer! 👆";
                this.showIdleHint = true;
            } else if (elapsed >= 30) {
                this.idleHintText = "Need help? Tap any picture! 💛";
                this.showIdleHint = true;
            }
            this.idleTimer = setTimeout(() => this.checkIdle(), 5000);
        },

        // ---- MATCHING LOGIC (QT-03) ----
        initMatching() {
            this.matchSelectedSide = null;
            this.matchSelectedIndex = null;
            this.matchedPairs = [];
            this.matchWrongPair = null;

            // Split options into Left Column and Right Column
            let leftOpts = this.currentQuestion.options.filter(o => o.is_correct === false);
            let rightOpts = this.currentQuestion.options.filter(o => o.is_correct === true);

            // If options weren't split by is_correct, split 50/50: first 2 left, last 2 right!
            if (leftOpts.length === 0 || rightOpts.length === 0) {
                const half = Math.ceil(this.currentQuestion.options.length / 2);
                leftOpts = this.currentQuestion.options.slice(0, half);
                rightOpts = this.currentQuestion.options.slice(half);
            }

            this.matchLeftItems = leftOpts.map((o, i) => ({
                text: o.text,
                image: o.image,
                matchKey: o.match_key || o.matchKey,
                originalIndex: i,
            }));

            const rightMapped = rightOpts.map((o, i) => ({
                text: o.text,
                image: o.image,
                matchKey: o.match_key || o.matchKey,
                originalIndex: i,
            }));

            this.matchRightItems = this.shuffleArray([...rightMapped]);
        },

        shuffleArray(arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr;
        },

        selectMatch(side, index) {
            this.resetIdleTimer();
            if (this.answered) return;

            // If already matched, ignore
            if (side === 'left' && this.matchedPairs.some(p => p.leftIndex === index)) return;
            if (side === 'right' && this.matchedPairs.some(p => p.rightIndex === index)) return;

            // If clicking same side, just switch selection
            if (this.matchSelectedSide === side) {
                this.matchSelectedIndex = index;
                return;
            }

            // If nothing selected yet, select this
            if (this.matchSelectedSide === null) {
                this.matchSelectedSide = side;
                this.matchSelectedIndex = index;
                return;
            }

            // We have a selection on the other side — check match!
            const leftIndex = side === 'left' ? index : this.matchSelectedIndex;
            const rightIndex = side === 'right' ? index : this.matchSelectedIndex;

            const leftOpt = this.matchLeftItems[leftIndex];
            const rightItem = this.matchRightItems[rightIndex];

            const leftKey = leftOpt.matchKey || leftOpt.match_key;
            const rightKey = rightItem.matchKey || rightItem.match_key;

            if (leftKey && rightKey && leftKey === rightKey) {
                // Correct match!
                this.matchedPairs.push({
                    leftIndex: leftIndex,
                    rightIndex: rightIndex,
                    matchKey: leftKey,
                });

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                        index: leftIndex, firstTry: true,
                    });
                }

                this.spawnConfetti(8);
                this.leoCelebrating = true;
                this.leoMessage = "Perfect match! 🎉";
                setTimeout(() => { this.leoCelebrating = false; }, 400);

                this.matchSelectedSide = null;
                this.matchSelectedIndex = null;

                // Check if all matched
                if (this.matchedPairs.length === this.matchLeftItems.length) {
                    this.completeMatching();
                }
            } else {
                // Wrong match — flash red, then clear selection
                this.matchWrongPair = { leftIndex, rightIndex };
                this.leoMessage = "Oops! Try again! 💪";
                this.showHint = true;

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_INCORRECT, {
                        index: leftIndex, attempts: 1,
                    });
                }

                setTimeout(() => {
                    this.matchWrongPair = null;
                    this.matchSelectedSide = null;
                    this.matchSelectedIndex = null;
                }, 600);
            }
        },

        completeMatching() {
            this.answered = true;
            this.score++;
            this.leoCelebrating = true;
            this.leoMessage = "You matched them all! Amazing! ⭐";

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                all_matched: true,
                pairs: this.matchedPairs.length,
                correct: true,
            });

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                    index: -1, firstTry: true, allMatched: true,
                });
            }

            this.spawnConfetti(20);
            setTimeout(() => { this.leoCelebrating = false; }, 600);

            // ✨ AUTO-ADVANCE matching after 2s
            setTimeout(() => { this.nextQuestion(); }, 2000);
        },

        getMatchItemClass(side, index) {
            const isMatched = side === 'left'
                ? this.matchedPairs.some(p => p.leftIndex === index)
                : this.matchedPairs.some(p => p.rightIndex === index);

            if (isMatched) return 'matched';

            if (this.matchWrongPair) {
                if (side === 'left' && this.matchWrongPair.leftIndex === index) return 'wrong';
                if (side === 'right' && this.matchWrongPair.rightIndex === index) return 'wrong';
            }

            if (this.matchSelectedSide === side && this.matchSelectedIndex === index) {
                return 'selected';
            }

            return '';
        },

        // ---- SEQUENCE LOGIC (QT-05) ----
        initSequence() {
            this.seqAnswered = false;
            this.seqSelectedCard = null;
            // Build cards from options. The "correct order" is the order of options
            // as stored in the database (option order = correct sequence position).
            const opts = this.currentQuestion.options.map((o, i) => ({
                text: o.text, image: o.image, correctIndex: i,
            }));
            // Shuffle for display in the tray
            this.seqCards = this.shuffleArray([...opts]);
            // Build empty slots (one per card)
            this.seqSlots = opts.map(() => null);
        },

        selectSeqCard(cardIndex) {
            this.resetIdleTimer();
            if (this.seqAnswered) return;

            // If this card is already placed, remove it from its slot first
            const placedIn = this.seqSlots.indexOf(cardIndex);
            if (placedIn !== -1) {
                this.seqSlots[placedIn] = null;
            }

            // Toggle selection
            this.seqSelectedCard = (this.seqSelectedCard === cardIndex) ? null : cardIndex;
        },

        selectSeqSlot(slotIndex) {
            this.resetIdleTimer();
            if (this.seqAnswered) return;

            // If no card selected and slot is filled, remove the card back to tray
            if (this.seqSelectedCard === null) {
                if (this.seqSlots[slotIndex] !== null) {
                    this.seqSlots[slotIndex] = null;
                }
                return;
            }

            // If target slot already has a card, remove it back to tray first
            if (this.seqSlots[slotIndex] !== null) {
                this.seqSlots[slotIndex] = null;
            }

            // Place selected card into this slot
            this.seqSlots[slotIndex] = this.seqSelectedCard;
            this.seqSelectedCard = null;
        },

        getSeqCardClass(cardIndex) {
            if (this.seqAnswered) return 'used';
            if (this.seqSlots.includes(cardIndex)) return 'used';
            if (this.seqSelectedCard === cardIndex) return 'dragging';
            return '';
        },

        getSeqSlotClass(slotIndex) {
            const filled = this.seqSlots[slotIndex] !== null;
            if (this.seqAnswered) {
                const cardIdx = this.seqSlots[slotIndex];
                if (cardIdx === null) return '';
                const card = this.seqCards[cardIdx];
                return card.correctIndex === slotIndex ? 'correct filled' : 'incorrect filled';
            }
            return filled ? 'filled' : '';
        },

        checkSequence() {
            this.resetIdleTimer();
            if (this.seqAnswered) return;
            if (this.seqSlots.includes(null)) return;

            this.seqAnswered = true;
            this.answered = true;

            let allCorrect = true;
            for (let i = 0; i < this.seqSlots.length; i++) {
                const card = this.seqCards[this.seqSlots[i]];
                if (card.correctIndex !== i) {
                    allCorrect = false;
                    break;
                }
            }

            if (allCorrect) {
                this.score++;
                this.leoCelebrating = true;
                this.leoMessage = "Perfect order! You're amazing! ⭐";

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                        index: -1, firstTry: true,
                    });
                }

                this.spawnConfetti(20);
            } else {
                this.leoMessage = "Almost! Check the order and try again next time! 💪";

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_INCORRECT, {
                        index: -1, attempts: 1,
                    });
                }
            }

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                slots: [...this.seqSlots],
                correct: allCorrect,
                type: 'drag_sequence',
            });

            setTimeout(() => { this.leoCelebrating = false; }, 600);

            // ✨ AUTO-ADVANCE after 2.5s (so kid sees the green/red feedback)
            setTimeout(() => { this.nextQuestion(); }, 2500);
        },

        // ---- DRAG & SORT LOGIC (QT-04) ----
        // Tap-to-sort pattern: child taps an item, then taps a category bucket.
        // No drag-and-drop needed — works perfectly on touchscreens!
        initSorting() {
            this.sortAnswered = false;
            this.sortSelectedChip = null;
            this.sortSelectedBucket = null;

            // Use metadata buckets if available, else derive from options' match_key
            if (this.currentQuestion.metadata && this.currentQuestion.metadata.buckets && this.currentQuestion.metadata.buckets.length > 0) {
                this.sortCategories = this.currentQuestion.metadata.buckets.map(b => b.name);
            } else {
                const buckets = [...new Set(this.currentQuestion.options.map(o => o.match_key))];
                this.sortCategories = buckets.filter(b => b); // filter out nulls/empty
            }

            // Build chips from options. Each option has:
            //   - text (e.g., "🐷 Pig")
            //   - match_key = the correct category (e.g., "🚜 Farm")
            //   - metadata.categories = array of category labels
            this.sortChips = this.currentQuestion.options.map((o) => ({
                text: o.text,
                image: o.image,
                correctBucket: o.match_key,   // e.g., "🚜 Farm"
                bucket: null,                  // null = in tray (unsorted)
            }));

            // Shuffle the chips so they appear in random order in the tray
            this.sortChips = this.shuffleArray(this.sortChips);
        },

        selectSortChip(chipIndex) {
            this.resetIdleTimer();
            if (this.sortAnswered) return;
            // If chip is in a bucket, tapping it returns it to the tray
            if (this.sortChips[chipIndex].bucket !== null) {
                this.sortChips[chipIndex].bucket = null;
                return;
            }
            // Toggle selection
            this.sortSelectedChip = (this.sortSelectedChip === chipIndex) ? null : chipIndex;
        },

                selectSortBucket(bucketIndex) {
            this.resetIdleTimer();
            if (this.sortAnswered) return;

            // If no chip was explicitly selected, pick the active unsorted target chip!
            if (this.sortSelectedChip === null) {
                const activeIndex = this.sortChips.findIndex(c => c.bucket === null);
                if (activeIndex !== -1) {
                    this.sortSelectedChip = activeIndex;
                } else {
                    return;
                }
            }

            const categoryName = this.sortCategories[bucketIndex];

            // Place the selected chip into this bucket
            this.sortChips[this.sortSelectedChip].bucket = categoryName;
            this.sortSelectedChip = null;

            // Auto-submit when all chips are sorted
            if (!this.sortChips.some(c => c.bucket === null)) {
                setTimeout(() => { this.checkSorting(); }, 200);
            }
        },

        getSortChipClass(chipIndex) {
            if (this.sortSelectedChip === chipIndex) return 'selected';
            return '';
        },

        getSortBucketClass(bucketIndex) {
            const categoryName = this.sortCategories[bucketIndex];

            if (this.sortAnswered) {
                // Check if all chips in this bucket are correct
                const chipsInBucket = this.sortChips.filter(c => c.bucket === categoryName);
                const allCorrect = chipsInBucket.every(c => c.correctBucket === categoryName);
                return allCorrect ? 'correct' : 'incorrect';
            }

            // Highlight the bucket when a chip is selected (invites the child to tap)
            if (this.sortSelectedChip !== null) return 'highlight';
            return '';
        },

        getSortChipInBucketClass(chipIndex) {
            if (!this.sortAnswered) return '';
            const chip = this.sortChips[chipIndex];
            return chip.correctBucket === chip.bucket ? 'correct' : 'incorrect';
        },

        checkSorting() {
            this.resetIdleTimer();
            if (this.sortAnswered) return;
            if (this.sortChips.some(c => c.bucket === null)) return;

            this.sortAnswered = true;
            this.answered = true;

            let allCorrect = true;
            for (const chip of this.sortChips) {
                if (chip.correctBucket !== chip.bucket) {
                    allCorrect = false;
                    break;
                }
            }

            if (allCorrect) {
                this.score++;
                this.leoCelebrating = true;
                this.leoMessage = "Perfect sorting! You're a star! ⭐";

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                        index: -1, firstTry: true,
                    });
                }

                this.spawnConfetti(20);
            } else {
                this.leoMessage = "Almost! Check the boxes and try again next time! 💪";

                if (window.KidQuizEvents) {
                    window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_INCORRECT, {
                        index: -1, attempts: 1,
                    });
                }
            }

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                chips: [...this.sortChips],
                correct: allCorrect,
                type: 'drag_sort',
            });

            setTimeout(() => { this.leoCelebrating = false; }, 600);

            // ✨ AUTO-ADVANCE after 2.5s (so kid sees the green/red feedback)
            setTimeout(() => { this.nextQuestion(); }, 2500);
        },

        // ---- SPEAK & REPEAT LOGIC (QT-07) ----
        // REAL speech recognition via the Web Speech API.
        // Verifies the child actually said the target word.
        // Falls back to practice mode if the browser/device has no speech recognition
        // support (no points awarded in practice mode — fair to the child).
        //
        // KEY FLOW:
        //   1. App speaks the word out loud (text-to-speech) so the child hears it
        //   2. Child taps mic and repeats the word
        //   3. Speech recognition verifies accuracy

        // 🔊 Play the target word out loud using text-to-speech
        playTargetWord() {
            this.resetIdleTimer();
            if (this.speakCompleted) return;

            const word = this.speakTargetWord ||
                this.currentQuestion.metadata?.word ||
                '';

            if (this.currentQuestion.audio) {
                this.isSpeaking = true;
                this.speakStatus = '🔊 Listen carefully...';
                const audio = new Audio(this.currentQuestion.audio);
                audio.play().then(() => {
                    audio.onended = () => {
                        this.isSpeaking = false;
                        if (!this.speakCompleted) {
                            this.speakStatus = word ? `🎤 Now YOU say "${word}"!` : '🎤 Now YOU say the word!';
                        }
                    };
                }).catch(() => {
                    this.isSpeaking = false;
                    this.speakStatus = word ? `🎤 Now YOU say "${word}"!` : '🎤 Now YOU say the word!';
                });
                return;
            }

            if (!word) return;

            // Cancel any ongoing speech first
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();

            this.isSpeaking = true;
            this.speakStatus = '🔊 Listen carefully...';

            // Use a slightly slower rate and higher pitch for kid-friendliness
            if ('speechSynthesis' in window) {
                const utterance = new SpeechSynthesisUtterance(word);
                utterance.rate = 0.7;   // slow so kids can hear each sound
                utterance.pitch = 1.2;
                utterance.volume = 1.0;
                utterance.lang = 'en-US';

                // Try to pick a clear English voice
                const voices = window.speechSynthesis.getVoices();
                const preferredVoice = voices.find(v =>
                    v.lang.startsWith('en') && (v.name.includes('Female') || v.name.includes('Google') || v.name.includes('Samantha'))
                );
                if (preferredVoice) utterance.voice = preferredVoice;

                utterance.onend = () => {
                    this.isSpeaking = false;
                    // After the word plays, prompt the child to repeat
                    if (!this.speakCompleted) {
                        this.speakStatus = `🎤 Now YOU say "${word}"!`;
                        this.leoMessage = `Say "${word}"! 🎤`;
                    }
                };
                utterance.onerror = () => {
                    this.isSpeaking = false;
                    if (!this.speakCompleted) {
                        this.speakStatus = `🎤 Now YOU say "${word}"!`;
                    }
                };

                window.speechSynthesis.speak(utterance);
            } else {
                // No speech synthesis — just show the word prominently
                this.isSpeaking = false;
                this.speakStatus = `🎤 Say "${word}"!`;
            }
        },

        initSpeakRecognition() {
            // Reset per-question state
            this.speakListening = false;
            this.speakAttempts = 0;
            this.speakCompleted = false;
            this.speakDots = [false, false, false];
            this.speakLastHeard = '';
            clearTimeout(this.speakTimer);

            // Extract the target word from metadata, options, or prompt text
            const promptMatch = (this.currentQuestion.prompt || '').match(/(?:say|word)\s+(?:out\s+loud\s*:\s*|['"]?\s*)?([A-Za-z0-9\s]+?)(?:['"]|!|\.|\?|$)/i);
            const promptWord = promptMatch ? promptMatch[1].trim() : '';

            this.speakTargetWord = (
                this.currentQuestion.metadata?.word ||
                this.currentQuestion.options?.find(o => o.is_correct)?.text ||
                promptWord ||
                ''
            ).toLowerCase().trim();

            // Detect Web Speech API support
            const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.speakSupported = !!SR;

            if (this.speakSupported) {
                // Create a recognition instance per question
                this.speakRecognition = new SR();
                this.speakRecognition.lang = 'en-US';
                this.speakRecognition.interimResults = false;
                this.speakRecognition.maxAlternatives = 3;
                this.speakRecognition.continuous = false;

                // When speech is recognized, check if it matches
                this.speakRecognition.onresult = (event) => {
                    let bestTranscript = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        bestTranscript += event.results[i][0].transcript;
                    }
                    this.speakLastHeard = bestTranscript.toLowerCase().trim();
                    this.handleSpeakResult();
                };

                // When recognition ends on its own (timeout / silence)
                this.speakRecognition.onend = () => {
                    if (this.speakListening && !this.speakCompleted) {
                        this.speakListening = false;
                        // If no transcript was captured, count it as an attempt anyway
                        if (!this.speakLastHeard) {
                            this.handleSpeakResult();
                        }
                    }
                };

                // Handle errors gracefully (no mic, permission denied, etc.)
                this.speakRecognition.onerror = (event) => {
                    this.speakListening = false;
                    if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                        // User blocked mic access — switch to practice mode permanently
                        this.speakSupported = false;
                        this.speakStatus = '📵 No mic access — practice mode! Tap to try!';
                    } else if (event.error === 'no-speech') {
                        // No speech detected — count as an attempt
                        this.handleSpeakResult();
                    } else {
                        this.speakStatus = '⚠️ Mic issue — try again!';
                    }
                };

                this.speakStatus = '🔊 Listen to the word...';
            } else {
                // Browser doesn't support speech recognition — practice mode
                this.speakStatus = '🔊 Listen to the word...';
            }

            // 🔊 AUTO-PLAY the target word after a short delay so the child hears it immediately
            setTimeout(() => {
                if (!this.speakCompleted && this.currentQuestion.type === 'speak_repeat') {
                    this.playTargetWord();
                }
            }, 800);
        },

        // ▼▼ HOLD-TO-RECORD: press = start listening, release = stop & check ▼▼
        // Much more intuitive for kids than tap-to-toggle!
        startSpeakHold() {
            this.resetIdleTimer();
            if (this.speakCompleted || this.speakListening) return;

            // Cancel any playing word audio so it doesn't interfere with mic
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
            this.isSpeaking = false;

            // Start listening
            this.speakListening = true;
            this.speakLastHeard = '';

            if (this.speakSupported && this.speakRecognition) {
                // REAL speech recognition
                this.speakStatus = '🔴 Listening... Say the word!';
                try {
                    // Abort any previous session first, then start fresh
                    this.speakRecognition.abort();
                    setTimeout(() => {
                        try { this.speakRecognition.start(); } catch (e) {}
                    }, 100);
                } catch (e) {
                    try { this.speakRecognition.abort(); } catch (e2) {}
                    this.speakListening = false;
                    this.speakStatus = '⚠️ Try again!';
                }
                // Safety timeout: auto-stop after 8 seconds of holding
                this.speakTimer = setTimeout(() => {
                    if (this.speakListening) this.endSpeakHold();
                }, 8000);
            } else {
                // PRACTICE MODE (no speech API) — visual "listening" feedback
                this.speakStatus = '🔴 Listening... Say the word!';
                this.speakTimer = setTimeout(() => {
                    if (this.speakListening) this.endSpeakHold();
                }, 3000);
            }
        },

        endSpeakHold() {
            this.resetIdleTimer();
            if (this.speakCompleted || !this.speakListening) return;

            clearTimeout(this.speakTimer);
            this.speakListening = false;

            // Stop recognition — onresult/onend will fire and call handleSpeakResult()
            if (this.speakSupported && this.speakRecognition) {
                this.speakStatus = '⏳ Checking...';
                try {
                    this.speakRecognition.stop();
                } catch (e) {
                    // If stop fails, handle result directly
                    this.handleSpeakResult();
                }
                // Safety: if no result fires within 1.5s, handle it manually
                setTimeout(() => {
                    if (!this.speakCompleted && !this.answered && this.speakAttempts === 0) {
                        this.handleSpeakResult();
                    }
                }, 1500);
            } else {
                // PRACTICE MODE — count it as an attempt
                this.handleSpeakResult();
            }
        },

        // Process the result of one speech attempt
        handleSpeakResult() {
            if (this.speakCompleted) return;
            this.speakAttempts++;
            this.speakDots[this.speakAttempts - 1] = true;

            if (this.speakSupported && this.speakTargetWord) {
                // REAL mode: check if the child said the correct word
                // Fuzzy match: correct if target word appears anywhere in transcript
                // (e.g., transcript "the cat" matches target "cat")
                const saidCorrectly = this.speakLastHeard.includes(this.speakTargetWord);

                if (saidCorrectly) {
                    // 🎉 Correct! Complete the question immediately
                    this.speakCompleted = true;
                    this.score++;
                    this.answered = true;
                    this.speakStatus = `🎉 You said "${this.speakTargetWord}"! Amazing!`;
                    this.leoCelebrating = true;
                    this.leoMessage = 'Awesome speaking! ⭐';
                    this.spawnConfetti(20);

                    this.answerLog.push({
                        question_id: this.currentQuestion.id,
                        attempts: this.speakAttempts,
                        correct: true,
                        heard: this.speakLastHeard,
                        type: 'speak_repeat',
                    });

                    if (window.KidQuizEvents) {
                        window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                            index: -1, firstTry: this.speakAttempts === 1,
                        });
                    }

                    setTimeout(() => { this.leoCelebrating = false; }, 600);
                    setTimeout(() => { this.nextQuestion(); }, 2000);
                    return;
                }

                // Wrong word — give feedback
                if (this.speakAttempts >= 3) {
                    // After 3 wrong attempts, gently move on (no points)
                    this.speakCompleted = true;
                    this.answered = true;
                    this.speakStatus = `💪 Good try! The word was "${this.speakTargetWord}".`;
                    this.leoMessage = 'Keep practicing! Next one! 💛';

                    this.answerLog.push({
                        question_id: this.currentQuestion.id,
                        attempts: this.speakAttempts,
                        correct: false,
                        heard: this.speakLastHeard,
                        type: 'speak_repeat',
                    });

                    setTimeout(() => { this.nextQuestion(); }, 2000);
                } else {
                    // Encourage them to try again
                    const encouragements = [
                        `🤔 I heard "${this.speakLastHeard || '...'}". Try again!`,
                        `💪 Try saying "${this.speakTargetWord}"!`,
                        `🌟 One more time! You can do it!`,
                    ];
                    this.speakStatus = encouragements[this.speakAttempts - 1] || 'Try again!';
                    this.leoMessage = 'Give it another try! 🎤';
                }
            } else {
                // PRACTICE MODE (no speech recognition) — no accuracy check
                // Child just practices saying the word. No points awarded.
                if (this.speakAttempts >= 3) {
                    this.speakCompleted = true;
                    this.answered = true;
                    this.speakStatus = '🎉 Great practice! Keep speaking!';
                    this.leoMessage = 'Awesome effort! ⭐';
                    this.spawnConfetti(10);

                    this.answerLog.push({
                        question_id: this.currentQuestion.id,
                        attempts: this.speakAttempts,
                        correct: false,
                        practice_mode: true,
                        type: 'speak_repeat',
                    });

                    setTimeout(() => { this.nextQuestion(); }, 2000);
                } else {
                    const encouragements = [
                        '🌟 Great! Say it again!',
                        '💪 Good try! One more time!',
                        '🎉 Almost there!',
                    ];
                    this.speakStatus = encouragements[this.speakAttempts - 1] || 'Keep going!';
                    this.leoMessage = 'Good job! Try again! 🎤';
                }
            }
        },

        skipSpeak() {
            this.resetIdleTimer();
            if (this.speakCompleted) return;

            clearTimeout(this.speakTimer);
            if (this.speakRecognition && this.speakSupported) {
                try { this.speakRecognition.abort(); } catch (e) {}
            }
            this.speakListening = false;
            this.speakCompleted = true;
            this.answered = true;
            this.speakStatus = '⏭️ No problem! Let\'s continue!';
            this.leoMessage = 'That\'s okay! Next question! 💛';

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                attempts: this.speakAttempts,
                correct: false,
                skipped: true,
                type: 'speak_repeat',
            });

            // ✨ AUTO-ADVANCE after 1.5s
            setTimeout(() => { this.nextQuestion(); }, 1500);
        },

        // ---- TRACING (QT-12) — PRACTICE ONLY (no scoring) ----
        // Kids trace freely for practice. No accuracy judgment, no auto-complete.
        // A "Watch How!" button plays an animated stroke demo.
        // The child taps "I'm Done!" when finished — always encouraging.

        initTracingCanvas(canvasEl) {
            this.$nextTick(() => {
                const ctx = canvasEl.getContext('2d');
                const size = 300;
                canvasEl.width = size;
                canvasEl.height = size;
                this.tracingCanvasEl = canvasEl;
                this.drawTracingGuide(ctx, size);

                // Event listeners for touch + mouse
                let isDrawing = false;
                let lastPos = null;
                const getPos = (e) => {
                    const rect = canvasEl.getBoundingClientRect();
                    const sx = size / rect.width;
                    const sy = size / rect.height;
                    const px = e.touches ? e.touches[0].clientX : e.clientX;
                    const py = e.touches ? e.touches[0].clientY : e.clientY;
                    return { x: (px - rect.left) * sx, y: (py - rect.top) * sy };
                };
                const start = (e) => {
                    e.preventDefault(); isDrawing = true;
                    this.resetIdleTimer();
                    const p = getPos(e);
                    lastPos = p;
                    ctx.beginPath(); ctx.moveTo(p.x, p.y);
                };
                const move = (e) => {
                    if (!isDrawing) return;
                    e.preventDefault();
                    const p = getPos(e);
                    ctx.lineTo(p.x, p.y); ctx.stroke();
                    lastPos = p;
                };
                const end = (e) => {
                    if (!isDrawing) return;
                    e.preventDefault(); isDrawing = false;
                    this.tracingStrokes++;
                    lastPos = null;
                    // Encouraging feedback (no judgment!)
                    if (this.tracingStrokes === 1) {
                        this.tracingStatus = '✏️ Great! Keep going!';
                    } else {
                        this.tracingStatus = '🌟 Awesome tracing!';
                    }
                };

                canvasEl.addEventListener('touchstart', start, { passive: false });
                canvasEl.addEventListener('touchmove', move, { passive: false });
                canvasEl.addEventListener('touchend', end, { passive: false });
                canvasEl.addEventListener('mousedown', start);
                canvasEl.addEventListener('mousemove', move);
                canvasEl.addEventListener('mouseup', end);
                canvasEl.addEventListener('mouseleave', end);
            });
        },

        // Draw the guide letter (faint fill + dashed outline)
        drawTracingGuide(ctx, size) {
            // Set child stroke style (purple) for both image and text modes
            ctx.strokeStyle = '#7C3AED';
            ctx.lineWidth = 16;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            // If an image was uploaded for tracing, don't draw text!
            // The image is displayed via CSS background on the canvas.
            if (this.currentQuestion.image) {
                ctx.clearRect(0, 0, size, size);
                return;
            }

            // Otherwise, draw the text guide
            let character = this.currentQuestion.metadata?.character;
            if (!character) {
                const p = this.currentQuestion.prompt || '';
                if (p.length > 0 && p.length <= 5) {
                    character = p;
                } else {
                    // Try to guess from prompt like "Trace the letter B" or "Trace number 5"
                    const promptMatch = p.match(/(?:letter|number)\s+([a-zA-Z0-9])/i) 
                                     || p.match(/trace\s+([a-zA-Z0-9])/i);
                    character = promptMatch ? promptMatch[1].toUpperCase() : 'A';
                }
            }

            const fontSize = 200;
            const cx = size / 2;
            const cy = size / 2 + 10;

            ctx.clearRect(0, 0, size, size);
            ctx.font = `bold ${fontSize}px Arial, sans-serif`;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            ctx.fillStyle = 'rgba(196, 181, 253, 0.15)';
            ctx.fillText(character, cx, cy);

            ctx.strokeStyle = '#C4B5FD';
            ctx.lineWidth = 2;
            ctx.setLineDash([12, 8]);
            ctx.strokeText(character, cx, cy);
            ctx.setLineDash([]);

            // Restore child stroke style
            ctx.strokeStyle = '#7C3AED';
        },

        // ---- STROKE PATHS (real writing order) ----
        // Each character = array of strokes; each stroke = array of [x, y] points.
        // The animated pencil follows these exact paths in order.
        tracingStrokePaths() {
            return {
                // Letter A — 3 strokes: left leg, right leg, crossbar
                'A': [
                    [[85, 225], [150, 95]],          // left diagonal (bottom → top)
                    [[150, 95], [215, 225]],          // right diagonal (top → bottom)
                    [[108, 170], [192, 170]],         // crossbar (left → right)
                ],
                // Letter B — 3 strokes: spine, top bump, bottom bump
                'B': [
                    [[110, 95], [110, 225]],          // vertical spine (top → bottom)
                    [[110, 95], [165, 110], [180, 135], [165, 158], [110, 160]], // top bump
                    [[110, 160], [170, 175], [188, 205], [170, 225], [110, 225]], // bottom bump
                ],
                // Letter C — 1 stroke: curve top→left→bottom
                'C': [
                    [[195, 120], [160, 100], [120, 105], [95, 150], [100, 195], [130, 222], [175, 220], [200, 200]],
                ],
                // Number 1 — 2 strokes: small flag, main line
                '1': [
                    [[125, 125], [150, 100]],         // small diagonal at top
                    [[150, 100], [150, 225]],         // main vertical
                ],
                // Number 2 — 1 stroke: curve then diagonal base
                '2': [
                    [[115, 135], [140, 108], [175, 108], [195, 138], [170, 168], [115, 218], [200, 218]],
                ],
                // Number 3 — 1 continuous stroke
                '3': [
                    [[110, 120], [150, 100], [185, 118], [185, 150], [155, 162], [120, 158], [155, 162], [190, 182], [190, 212], [155, 228], [115, 220]],
                ],
            };
        },

        // Animated demo: pencil follows real stroke paths (accurate writing order)
        playTracingDemo() {
            if (!this.tracingCanvasEl || this.answered) return;
            this.resetIdleTimer();
            this.tracingDemoPlaying = true;
            this.tracingStatus = '👀 Watch how to write it!';

            const ctx = this.tracingCanvasEl.getContext('2d');
            const size = 300;
            let character = this.currentQuestion.metadata?.character;
            if (!character) {
                const promptMatch = this.currentQuestion.prompt?.match(/(?:letter|number)\s+([a-zA-Z0-9])/i) 
                                 || this.currentQuestion.prompt?.match(/trace\s+([a-zA-Z0-9])/i);
                character = promptMatch ? promptMatch[1].toUpperCase() : 'A';
            }

            // Get stroke paths; fall back to a simple down-stroke if undefined
            const allPaths = this.tracingStrokePaths();
            const strokes = allPaths[character] || [[[150, 100], [150, 220]]];

            // Precompute: total distance across all strokes, and per-segment lengths
            const segs = []; // {x1,y1,x2,y2,len,strokeIdx}
            let totalLen = 0;
            strokes.forEach((stroke, si) => {
                for (let i = 0; i < stroke.length - 1; i++) {
                    const [x1, y1] = stroke[i];
                    const [x2, y2] = stroke[i + 1];
                    const len = Math.hypot(x2 - x1, y2 - y1);
                    segs.push({ x1, y1, x2, y2, len, strokeIdx: si });
                    totalLen += len;
                }
            });

            const speed = 100; // pixels per second — slow so kids can follow along
            const duration = (totalLen / speed) * 1000; // ms
            const startTime = performance.now();
            let drawnUpTo = 0; // distance already committed to the canvas

            // Helper: draw all segments fully up to a given distance
            const drawAccumulated = (distLimit) => {
                let d = 0;
                ctx.strokeStyle = '#7C3AED';
                ctx.lineWidth = 16;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.setLineDash([]);
                strokes.forEach((stroke) => {
                    ctx.beginPath();
                    ctx.moveTo(stroke[0][0], stroke[0][1]);
                    let segD = 0;
                    let started = true;
                    for (let i = 0; i < stroke.length - 1; i++) {
                        const [x1, y1] = stroke[i];
                        const [x2, y2] = stroke[i + 1];
                        const segLen = Math.hypot(x2 - x1, y2 - y1);
                        if (d + segLen <= distLimit) {
                            ctx.lineTo(x2, y2);
                            d += segLen;
                        } else {
                            // partial segment
                            const remain = distLimit - d;
                            const t = segLen > 0 ? remain / segLen : 0;
                            const px = x1 + (x2 - x1) * t;
                            const py = y1 + (y2 - y1) * t;
                            ctx.lineTo(px, py);
                            d += remain;
                            started = false;
                            break;
                        }
                    }
                    ctx.stroke();
                });
            };

            const animate = (now) => {
                const elapsed = now - startTime;
                const dist = Math.min(totalLen, (elapsed / duration) * totalLen);

                // Redraw guide + accumulated strokes up to current distance
                this.drawTracingGuide(ctx, size);
                drawAccumulated(dist);

                // Find current pencil position (at the leading edge)
                let d = 0, px = 0, py = 0;
                for (const s of segs) {
                    if (d + s.len >= dist) {
                        const t = s.len > 0 ? (dist - d) / s.len : 0;
                        px = s.x1 + (s.x2 - s.x1) * t;
                        py = s.y1 + (s.y2 - s.y1) * t;
                        break;
                    }
                    d += s.len;
                    px = s.x2; py = s.y2;
                }

                // Draw the moving pencil at the leading edge
                ctx.save();
                ctx.font = '32px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('✏️', px, py - 4);
                ctx.restore();

                if (dist < totalLen) {
                    requestAnimationFrame(animate);
                } else {
                    // Demo complete — hold for a moment, then clear for child to try
                    setTimeout(() => {
                        this.drawTracingGuide(ctx, size);
                        this.tracingDemoPlaying = false;
                        this.tracingStrokes = 0;
                        this.tracingStatus = '✏️ Now you try!';
                    }, 900);
                }
            };
            requestAnimationFrame(animate);
        },

        clearTracing() {
            if (!this.tracingCanvasEl || this.answered) return;
            this.resetIdleTimer();
            const ctx = this.tracingCanvasEl.getContext('2d');
            const size = 300;
            this.drawTracingGuide(ctx, size);
            this.tracingStrokes = 0;
            this.tracingStatus = '✏️ Try again!';
        },

        // "I'm Done!" — always encouraging, no score (practice activity)
        doneTracing() {
            if (this.answered) return;
            this.resetIdleTimer();
            this.tracingCompleted = true;
            this.answered = true;
            this.leoCelebrating = true;
            this.leoMessage = 'Great practice! ✏️ Try on paper too! 📝';

            if (window.KidQuizEvents) {
                window.KidQuizEvents.emit(window.KidQuizEvents.EVENTS.ANSWER_CORRECT, {
                    index: -1, firstTry: true,
                });
            }
            this.spawnConfetti(15);

            this.answerLog.push({
                question_id: this.currentQuestion.id,
                correct: true,
                type: 'tracing',
                practice: true,
                strokes: this.tracingStrokes,
            });

            setTimeout(() => { this.nextQuestion(); }, 2000);
        },

        // ---- SUBMIT QUIZ (via AJAX JSON POST -> Returns HTTP 200 OK) ----
        async submitQuiz() {
            console.log('%c 🚀 [QUIZ-ENGINE] Submitting Quiz Results via Fetch JSON AJAX...', 'background: #22c55e; color: white; font-size: 14px; font-weight: bold; padding: 4px 8px; border-radius: 4px;', {
                action: config.submitUrl,
                score: this.score,
                total: this.questions.length,
                stars: this.starsEarned,
                timeSpent: this.timeSpent
            });

            try {
                const response = await fetch(config.submitUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrfToken
                    },
                    body: JSON.stringify({
                        _token: config.csrfToken,
                        score: this.score,
                        total: this.questions.length,
                        stars: this.starsEarned,
                        time_spent: this.timeSpent,
                        answers: JSON.stringify(this.answerLog)
                    })
                });

                const data = await response.json();
                console.log('✅ BACKEND RESPONSE STATUS:', response.status, data);

                if (response.ok && data.success) {
                    // Navigate cleanly to celebration screen
                    window.location.href = data.redirect_url || '/celebration';
                } else {
                    console.error('❌ SUBMISSION FAILED:', data);
                    alert('Server error saving quiz: ' + (data.error || JSON.stringify(data)));
                }
            } catch (err) {
                console.error('❌ FETCH NETWORK ERROR:', err);
                alert('Network error submitting quiz: ' + err.message);
            }
        },

        // ---- EXIT / RESTART ----
        exitQuiz() {
            if (confirm('Leave the quiz? Your progress will be saved.')) {
                window.location.href = config.exitUrl;
            }
        },

        restartQuiz() {
            this.currentIndex = 0;
            this.score = 0;
            this.starsEarned = 0;
            this.quizComplete = false;
            this.answerLog = [];
            this.startTime = Date.now();
            if (window.KidRewardLayer) window.KidRewardLayer.reset();
            this.startQuestion();
        },

        // ---- CONFETTI ----
        spawnConfetti(count) {
            const colors = ['#FBBF24', '#F472B6', '#A78BFA', '#34D399', '#60A5FA', '#F87171'];
            for (let i = 0; i < count; i++) {
                const c = document.createElement('div');
                c.className = 'confetti-piece';
                c.style.left = Math.random() * 100 + 'vw';
                c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                c.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                c.style.animation = `confetti-fall ${1 + Math.random()}s ease-out forwards`;
                c.style.animationDelay = Math.random() * 0.3 + 's';
                document.body.appendChild(c);
                setTimeout(() => c.remove(), 3000);
            }
        },
    };
}
