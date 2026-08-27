<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>🎤 Speak & Repeat — Mic Test</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Comic Sans MS', 'Baloo 2', system-ui, sans-serif;
            background: linear-gradient(180deg, #FEF3C7 0%, #FDE68A 50%, #FBCFE8 100%);
            min-height: 100vh; display: flex; flex-direction: column; align-items: center;
            padding: 20px;
        }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 32px; color: #7C3AED; text-shadow: 2px 2px 0 white; }
        .header p { font-size: 16px; color: #6B7280; }

        .word-card {
            background: white; border-radius: 24px; padding: 32px 40px;
            text-align: center; margin: 20px auto; max-width: 360px;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.15);
            animation: pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .word-emoji { font-size: 80px; line-height: 1; margin-bottom: 8px; }
        .word-text {
            font-size: 42px; font-weight: 900; color: #1F2937;
            letter-spacing: 0.02em;
        }

        .mic-btn {
            width: 180px; height: 180px; border-radius: 50%;
            background: linear-gradient(135deg, #7C3AED, #A78BFA);
            border: none; cursor: pointer; display: flex; flex-direction: column;
            align-items: center; justify-content: center; margin: 20px auto;
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.3);
            transition: transform 0.15s ease; position: relative;
            animation: fade-up 0.4s ease-out;
        }
        .mic-btn:hover { transform: scale(1.05); }
        .mic-btn:active { transform: scale(0.95); }
        .mic-btn .mic-icon { font-size: 64px; line-height: 1; }
        .mic-btn .mic-label { font-weight: 900; font-size: 14px; color: white; margin-top: 4px; }
        .mic-btn.listening {
            animation: mic-pulse 0.8s ease infinite;
            background: linear-gradient(135deg, #EF4444, #F87171);
        }
        @keyframes mic-pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3); }
            50% { transform: scale(1.1); box-shadow: 0 12px 40px rgba(239, 68, 68, 0.5); }
        }
        .mic-btn .mic-waves {
            position: absolute; inset: -8px; border-radius: 50%;
            border: 3px solid rgba(239, 68, 68, 0.3); opacity: 0;
            animation: mic-wave 1s ease-out infinite;
        }
        .mic-btn.listening .mic-waves { opacity: 1; }
        @keyframes mic-wave {
            0% { transform: scale(1); opacity: 0.6; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        .status {
            font-size: 22px; font-weight: 700; color: #1F2937;
            min-height: 32px; text-align: center; margin: 10px 0;
        }
        .status.success { color: #16A34A; }
        .status.error { color: #DC2626; }
        .status.listening { color: #7C3AED; }

        .heard-text {
            background: white; border-radius: 12px; padding: 16px 24px;
            text-align: center; margin: 12px auto; max-width: 360px;
            font-size: 20px; font-weight: 600; color: #6B7280;
            min-height: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .heard-text.match { color: #16A34A; background: #DCFCE7; border: 2px solid #86EFAC; }
        .heard-text.mismatch { color: #DC2626; background: #FEE2E2; border: 2px solid #FCA5A5; }

        .nav-btn {
            padding: 16px 40px; border-radius: 50px;
            background: #7C3AED; color: white; font-size: 20px; font-weight: 900;
            border: none; cursor: pointer; box-shadow: 0 4px 0 rgba(0,0,0,0.15);
            transition: all 0.15s ease; margin-top: 16px; display: none;
        }
        .nav-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 0 rgba(0,0,0,0.15); }
        .nav-btn:active { transform: translateY(2px); box-shadow: 0 2px 0 rgba(0,0,0,0.15); }
        .nav-btn.visible { display: inline-block; animation: fade-up 0.3s ease; }

        .progress { display: flex; gap: 8px; justify-content: center; margin: 10px 0; }
        .dot { width: 16px; height: 16px; border-radius: 50%; background: #E5E7EB; transition: all 0.2s; }
        .dot.done { background: #22C55E; transform: scale(1.3); }
        .dot.current { background: #7C3AED; transform: scale(1.3); }

        .info-box {
            background: rgba(255,255,255,0.7); border-radius: 12px;
            padding: 12px 20px; margin: 16px auto; max-width: 400px;
            font-size: 14px; color: #6B7280; text-align: center;
        }
        .info-box code { background: #EDE9FE; padding: 2px 6px; border-radius: 4px; font-size: 12px; }

        @keyframes pop-in { 0% { opacity:0; transform: scale(0.8); } 100% { opacity:1; transform: scale(1); } }
        @keyframes fade-up { 0% { opacity:0; transform: translateY(20px); } 100% { opacity:1; transform: translateY(0); } }
    </style>
</head>
<body>

    <div class="header">
        <h1>🎤 Speak & Repeat</h1>
        <p>Say the word out loud!</p>
    </div>

    <div class="progress" id="progress"></div>

    <div class="word-card" id="word-card">
        <div class="word-emoji" id="word-emoji">🐱</div>
        <div class="word-text" id="word-text">Cat</div>
    </div>

    <button class="mic-btn" id="mic-btn" onclick="toggleMic()">
        <div class="mic-waves"></div>
        <div class="mic-icon">🎤</div>
        <div class="mic-label" id="mic-label">TAP TO TALK</div>
    </button>

    <div class="status" id="status">Tap the microphone and say the word!</div>
    <div class="heard-text" id="heard-text">I'm listening...</div>

    <button class="nav-btn" id="next-btn" onclick="nextWord()">Next Word ➡️</button>

    <div class="info-box">
        🔒 Mic only works on HTTPS. You're on: <code id="protocol">...</code>
    </div>

<script>
const WORDS = [
    { emoji: '🐱', text: 'cat' },
    { emoji: '🐶', text: 'dog' },
    { emoji: '☀️', text: 'sun' },
    { emoji: '🍎', text: 'apple' },
    { emoji: '⭐', text: 'star' },
];

let currentIndex = 0;
let recognition = null;
let isListening = false;

// Check HTTPS
document.getElementById('protocol').textContent = window.location.protocol + '//' + window.location.host;

// Initialize Speech Recognition
function initSpeech() {
    const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) {
        document.getElementById('status').textContent = '❌ Voice recognition not supported on this browser';
        document.getElementById('status').className = 'status error';
        document.getElementById('mic-btn').style.opacity = '0.4';
        document.getElementById('mic-btn').style.pointerEvents = 'none';
        return false;
    }

    recognition = new SR();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';
    recognition.maxAlternatives = 3;

    recognition.onresult = (event) => {
        let transcript = '';
        for (let i = 0; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        document.getElementById('heard-text').textContent = '"' + transcript + '"';

        if (event.results[event.results.length - 1].isFinal) {
            checkAnswer(transcript.trim().toLowerCase());
        }
    };

    recognition.onerror = (event) => {
        console.error('Speech error:', event.error);
        let msg = '❌ Mic error: ' + event.error;
        if (event.error === 'not-allowed') msg = '🔇 Microphone access denied. Please allow it in browser settings.';
        if (event.error === 'no-speech') msg = '🤔 Didn\'t hear anything. Try again!';
        document.getElementById('status').textContent = msg;
        document.getElementById('status').className = 'status error';
        stopListening();
    };

    recognition.onend = () => {
        stopListening();
    };

    return true;
}

function checkAnswer(spoken) {
    const target = WORDS[currentIndex].text.toLowerCase();
    const heardEl = document.getElementById('heard-text');
    const statusEl = document.getElementById('status');

    // Check if the target word is in what they said
    if (spoken.includes(target) || target.includes(spoken)) {
        statusEl.textContent = '🎉 Perfect! You said it right!';
        statusEl.className = 'status success';
        heardEl.className = 'heard-text match';
        document.getElementById('next-btn').classList.add('visible');
        // Mark dot as done
        const dots = document.querySelectorAll('.dot');
        if (dots[currentIndex]) dots[currentIndex].classList.add('done');
    } else {
        statusEl.textContent = '🤔 Hmm, I heard "' + spoken + '". Try saying "' + target + '"!';
        statusEl.className = 'status error';
        heardEl.className = 'heard-text mismatch';
    }
}

function toggleMic() {
    if (!recognition && !initSpeech()) return;

    if (isListening) {
        recognition.stop();
        stopListening();
    } else {
        // Clear previous
        document.getElementById('heard-text').textContent = '';
        document.getElementById('heard-text').className = 'heard-text';
        document.getElementById('status').textContent = '🎧 Listening...';
        document.getElementById('status').className = 'status listening';
        document.getElementById('next-btn').classList.remove('visible');

        try {
            recognition.start();
            isListening = true;
            document.getElementById('mic-btn').classList.add('listening');
            document.getElementById('mic-label').textContent = 'LISTENING...';
        } catch (e) {
            console.error(e);
            document.getElementById('status').textContent = '❌ Could not start mic. Try again.';
        }
    }
}

function stopListening() {
    isListening = false;
    document.getElementById('mic-btn').classList.remove('listening');
    document.getElementById('mic-label').textContent = 'TAP TO TALK';
}

function nextWord() {
    currentIndex = (currentIndex + 1) % WORDS.length;
    const word = WORDS[currentIndex];
    document.getElementById('word-emoji').textContent = word.emoji;
    document.getElementById('word-text').textContent = word.text.charAt(0).toUpperCase() + word.text.slice(1);

    // Reset
    document.getElementById('heard-text').textContent = 'I\'m listening...';
    document.getElementById('heard-text').className = 'heard-text';
    document.getElementById('status').textContent = 'Tap the microphone and say the word!';
    document.getElementById('status').className = 'status';
    document.getElementById('next-btn').classList.remove('visible');

    // Restart card animation
    const card = document.getElementById('word-card');
    card.style.animation = 'none';
    void card.offsetWidth;
    card.style.animation = 'pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';

    updateProgress();
}

function updateProgress() {
    const progress = document.getElementById('progress');
    progress.innerHTML = '';
    WORDS.forEach((_, i) => {
        const dot = document.createElement('div');
        dot.className = 'dot';
        if (i < currentIndex) dot.classList.add('done');
        if (i === currentIndex) dot.classList.add('current');
        progress.appendChild(dot);
    });
}

// Initialize on load
window.addEventListener('DOMContentLoaded', () => {
    initSpeech();
    updateProgress();
});
</script>
</body>
</html>