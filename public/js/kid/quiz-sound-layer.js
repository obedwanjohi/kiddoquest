/**
 * ============================================================
 * KID UI — SOUND LAYER
 * Plays appropriate audio for quiz events.
 * Knows NOTHING about quizzes — only listens to events.
 *
 * Uses Web Audio API to generate sounds procedurally.
 * No audio files needed. All sounds are gentle and kid-safe.
 * ============================================================
 */
window.KidSoundLayer = (function () {
    let audioCtx = null;
    let muted = false;
    let volume = 0.6; // 60% default

    // Lazy-init AudioContext (browsers require user gesture)
    function init() {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }
        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
    }

    /**
     * Play a pleasant tone with given frequencies and timing.
     * @param {number[]} frequencies - Array of frequencies in Hz
     * @param {number} duration - Total duration in seconds
     * @param {string} type - Wave type: 'sine', 'triangle', 'square'
     * @param {number} startDelay - Delay before playing (seconds)
     */
    function playTone(frequencies, duration = 0.2, type = 'sine', startDelay = 0) {
        if (muted || !audioCtx) return;

        const now = audioCtx.currentTime + startDelay;
        const noteDuration = duration / frequencies.length;

        frequencies.forEach((freq, i) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();

            osc.type = type;
            osc.frequency.value = freq;

            // Envelope: quick attack, gentle decay (never harsh)
            const noteStart = now + (i * noteDuration);
            const noteEnd = noteStart + noteDuration;

            gain.gain.setValueAtTime(0, noteStart);
            gain.gain.linearRampToValueAtTime(volume * 0.3, noteStart + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, noteEnd);

            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.start(noteStart);
            osc.stop(noteEnd);
        });
    }

    // Procedural handclap for applause effect
    function playClap(time) {
        if (!audioCtx) return;
        const bufferSize = audioCtx.sampleRate * 0.15; // 150ms
        const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
        const data = buffer.getChannelData(0);
        for (let i = 0; i < bufferSize; i++) {
            data[i] = Math.random() * 2 - 1; // white noise
        }
        const noise = audioCtx.createBufferSource();
        noise.buffer = buffer;

        const filter = audioCtx.createBiquadFilter();
        filter.type = 'bandpass';
        filter.frequency.value = 1200; // clap frequency

        const gain = audioCtx.createGain();
        gain.gain.setValueAtTime(volume * 0.5, time);
        gain.gain.exponentialRampToValueAtTime(0.001, time + 0.1);

        noise.connect(filter);
        filter.connect(gain);
        gain.connect(audioCtx.destination);
        noise.start(time);
    }

    // Helper to play custom random audio with a fallback to the synth
    function playCustomSound(type, count, fallbackSynth) {
        if (muted) return;
        // Randomly pick a number between 1 and count
        const randomNum = Math.floor(Math.random() * count) + 1;
        const filePath = `/sounds/${type}/${randomNum}.mp3`;
        
        const audio = new Audio(filePath);
        audio.play().catch(e => {
            // If the file doesn't exist or fails to play, use our built-in sounds!
            fallbackSynth();
        });
    }

    // ---- SOUND PRESETS (all gentle, kid-safe) ----
    const sounds = {
        // Correct answer: Tries to play /sounds/correct/1.mp3 to 5.mp3
        correct() {
            playCustomSound('correct', 5, () => {
                // Celebration Fanfare Fallback
                playTone([523.25, 659.25, 783.99], 0.2, 'triangle');
                playTone([1046.50], 0.6, 'sine', 0.2);
                
                // Generate a crowd applause effect (20 claps over 1.5 seconds)
                if (!muted && audioCtx) {
                    const now = audioCtx.currentTime;
                    for(let i = 0; i < 20; i++) {
                        playClap(now + (Math.random() * 1.2));
                    }
                }
            });
        },

        // Wrong answer: Tries to play /sounds/wrong/1.mp3 to 5.mp3
        wrong() {
            playCustomSound('wrong', 5, () => {
                // Fallback: gentle descending two-note
                playTone([392.00, 349.23], 0.3, 'sine');
            });
        },

        // Tap/select: soft click
        tap() {
            playTone([880], 0.08, 'sine');
        },

        // Hover: very subtle (optional, very quiet)
        hover() {
            playTone([1200], 0.04, 'sine');
        },

        // Star earned: magical sparkle (high arpeggio)
        star() {
            playTone([1046.50, 1318.51, 1567.98], 0.3, 'triangle');
        },

        // Celebration: fanfare (C-E-G major chord arpeggio up)
        celebration() {
            playTone([523.25, 659.25, 783.99], 0.15, 'triangle');
            playTone([1046.50], 0.4, 'triangle', 0.45);
        },

        // Hint appears: gentle attention chime
        hint() {
            playTone([659.25, 783.99], 0.2, 'sine');
        },

        // Reveal answer: warm revelation
        reveal() {
            playTone([523.25, 659.25, 783.99], 0.5, 'sine');
        },

        // Question appears: playful enter sound
        questionEnter() {
            playTone([587.33, 783.99], 0.15, 'triangle');
        },

        // Quiz complete: triumphant but gentle
        quizComplete() {
            playTone([523.25, 659.25, 783.99, 1046.50, 1318.51], 0.8, 'triangle');
        },

        // Button click: satisfying press
        buttonClick() {
            playTone([600], 0.06, 'square');
        },
    };

    let isConnected = false;

    /**
     * Initialize the sound layer by subscribing to events.
     * Call this once on page load.
     */
    function connect() {
        if (isConnected) return;
        isConnected = true;
        
        const E = window.KidQuizEvents.EVENTS;

        window.KidQuizEvents.on(E.ANSWER_CORRECT, () => sounds.correct());
        window.KidQuizEvents.on(E.ANSWER_INCORRECT, () => sounds.wrong());
        window.KidQuizEvents.on(E.ANSWER_SELECTED, () => sounds.tap());
        window.KidQuizEvents.on(E.HINT_SHOWN, () => sounds.hint());
        window.KidQuizEvents.on(E.ANSWER_REVEALED, () => sounds.reveal());
        window.KidQuizEvents.on(E.QUESTION_STARTED, () => sounds.questionEnter());
        window.KidQuizEvents.on(E.QUIZ_COMPLETED, () => sounds.quizComplete());
        window.KidQuizEvents.on(E.STAR_EARNED, () => sounds.star());
        window.KidQuizEvents.on(E.CELEBRATION_TRIGGERED, () => sounds.celebration());
        window.KidQuizEvents.on(E.NEXT_QUESTION, () => sounds.buttonClick());
    }

    function setMuted(value) { muted = value; }
    function setVolume(value) { volume = Math.max(0, Math.min(1, value)); }
    function isMuted() { return muted; }

    return { init, connect, sounds, setMuted, setVolume, isMuted };
})();