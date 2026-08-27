{{--
    Phase 9: "Preview as Child" — Kid-facing lesson renderer
    Standalone layout (no admin sidebar) — full-screen, colorful, big buttons
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Preview: {{ $lesson->title }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Comic Sans MS','Segoe UI',sans-serif; }
        body { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); min-height:100vh; overflow-x:hidden; }
        
        /* Admin bar (thin strip so reviewer knows they're in preview) */
        .admin-bar { background:#1e293b; color:#94a3b8; padding:6px 24px; font-size:11px; display:flex; justify-content:space-between; align-items:center; }
        .admin-bar a { color:#818cf8; text-decoration:none; font-weight:bold; }
        .admin-bar .pill { background:#4f46e5; color:white; padding:2px 10px; border-radius:12px; font-size:10px; }

        /* Screens */
        .screen { display:none; min-height:calc(100vh - 32px); flex-direction:column; align-items:center; justify-content:center; padding:40px 20px; }
        .screen.active { display:flex; }

        /* Intro Screen */
        .intro-card { background:white; border-radius:32px; padding:48px; max-width:600px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:pop 0.5s ease; }
        @keyframes pop { 0% { transform:scale(0.8); opacity:0; } 100% { transform:scale(1); opacity:1; } }
        .intro-icon { font-size:80px; margin-bottom:16px; }
        .intro-card h1 { font-size:32px; color:#1e293b; margin-bottom:8px; }
        .intro-card .subject { font-size:14px; color:#64748b; margin-bottom:24px; }
        .intro-card .summary { font-size:16px; color:#475569; line-height:1.6; margin-bottom:32px; }
        .lesson-stats { display:flex; gap:16px; justify-content:center; margin-bottom:32px; }
        .stat-box { background:#f1f5f9; border-radius:16px; padding:16px 24px; }
        .stat-box .num { font-size:28px; font-weight:bold; color:#4f46e5; }
        .stat-box .label { font-size:12px; color:#64748b; }

        /* Buttons */
        .btn-big { border:none; border-radius:20px; padding:20px 48px; font-size:22px; font-weight:bold; cursor:pointer; transition:all 0.2s; font-family:inherit; }
        .btn-big:active { transform:scale(0.95); }
        .btn-go { background:linear-gradient(135deg,#22c55e,#16a34a); color:white; box-shadow:0 8px 24px rgba(34,197,94,0.4); }
        .btn-go:hover { box-shadow:0 12px 32px rgba(34,197,94,0.5); }
        .btn-next { background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; box-shadow:0 8px 24px rgba(59,130,246,0.4); }
        .btn-exit { background:white; color:#64748b; padding:10px 20px; font-size:14px; border-radius:12px; border:none; cursor:pointer; text-decoration:none; }

        /* Movie Screen */
        .movie-wrap { max-width:800px; width:100%; }
        .movie-card { background:black; border-radius:24px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,0.4); }
        .movie-card video, .movie-card iframe { width:100%; height:450px; display:block; }
        .movie-info { background:white; padding:24px; border-radius:0 0 24px 24px; }
        .movie-info h2 { color:#1e293b; margin-bottom:8px; }
        .movie-info p { color:#64748b; font-size:14px; }
        .movie-actions { display:flex; gap:16px; justify-content:center; margin-top:24px; }

        /* Quiz Screen */
        .quiz-card { background:white; border-radius:32px; padding:40px; max-width:700px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:pop 0.4s ease; }
        .quiz-progress { display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; }
        .quiz-progress .q-num { font-size:14px; color:#64748b; font-weight:bold; }
        .quiz-progress .bar { flex:1; height:12px; background:#e2e8f0; border-radius:6px; margin:0 16px; overflow:hidden; }
        .quiz-progress .fill { height:100%; background:linear-gradient(90deg,#4f46e5,#7c3aed); border-radius:6px; transition:width 0.4s ease; }
        .quiz-type-badge { display:inline-block; background:#ede9fe; color:#6d28d9; padding:4px 14px; border-radius:12px; font-size:12px; font-weight:bold; margin-bottom:16px; }
        .quiz-prompt { font-size:24px; color:#1e293b; text-align:center; margin-bottom:8px; line-height:1.4; }
        .quiz-prompt-img { max-width:200px; border-radius:16px; margin:0 auto 20px; display:block; }
        .quiz-hint { text-align:center; color:#64748b; font-size:14px; margin-bottom:24px; font-style:italic; }

        /* Options */
        .options-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
        .option-btn { background:#f1f5f9; border:3px solid transparent; border-radius:20px; padding:24px; font-size:22px; font-weight:bold; cursor:pointer; transition:all 0.2s; text-align:center; font-family:inherit; color:#1e293b; }
        .option-btn:hover { background:#e0e7ff; border-color:#a5b4fc; transform:translateY(-2px); }
        .option-btn.selected { background:#dbeafe; border-color:#3b82f6; }
        .option-btn.correct { background:#dcfce7; border-color:#22c55e; animation:bounce 0.5s; }
        .option-btn.wrong { background:#fef2f2; border-color:#ef4444; animation:shake 0.5s; }
        .option-btn img { max-width:80px; display:block; margin:0 auto 8px; }
        @keyframes bounce { 0%,100% { transform:scale(1); } 50% { transform:scale(1.1); } }
        @keyframes shake { 0%,100% { transform:translateX(0); } 25% { transform:translateX(-8px); } 75% { transform:translateX(8px); } }

        .feedback { text-align:center; margin-top:24px; font-size:20px; font-weight:bold; min-height:30px; }
        .feedback.correct { color:#22c55e; }
        .feedback.wrong { color:#ef4444; }
        .quiz-actions { display:flex; gap:12px; justify-content:center; margin-top:20px; }

        /* Result Screen */
        .result-card { background:white; border-radius:32px; padding:48px; max-width:500px; width:100%; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3); animation:pop 0.5s ease; }
        .result-emoji { font-size:80px; margin-bottom:16px; }
        .score-circle { width:160px; height:160px; border-radius:50%; margin:0 auto 24px; display:flex; flex-direction:column; align-items:center; justify-content:center; border:8px solid; }
        .score-circle .score-num { font-size:48px; font-weight:bold; line-height:1; }
        .score-circle .score-pct { font-size:16px; }
        .result-stars { font-size:36px; margin-bottom:16px; letter-spacing:8px; }
        .result-msg { font-size:20px; color:#1e293b; margin-bottom:32px; }

        @media (max-width:600px) {
            .options-grid { grid-template-columns:1fr; }
            .quiz-card, .intro-card, .result-card { padding:24px; }
            .quiz-prompt { font-size:18px; }
        }
    </style>
</head>
<body>

{{-- Admin bar --}}
<div class="admin-bar">
    <span>👁️ <strong>PREVIEW MODE</strong> — This is exactly what a child sees. No progress is saved.</span>
    <span>
        <span class="pill">{{ $lesson->status }}</span>
        <a href="{{ route('admin.lessons.show', $lesson) }}">← Exit to Admin</a>
    </span>
</div>

{{-- ═══ INTRO SCREEN ═══ --}}
<div class="screen active" id="screen-intro">
    <div class="intro-card">
        <div class="intro-icon">{{ $lesson->topic->subject->icon ?? '📚' }}</div>
        <h1>{{ $lesson->title }}</h1>
        <div class="subject">{{ $lesson->topic->subject->name ?? '' }} → {{ $lesson->topic->name ?? '' }}</div>
        @if ($lesson->summary)
            <p class="summary">{{ $lesson->summary }}</p>
        @endif
        <div class="lesson-stats">
            <div class="stat-box">
                <div class="num">🎬</div>
                <div class="label">{{ $lesson->duration_minutes }} min</div>
            </div>
            <div class="stat-box">
                <div class="num">{{ $totalQuestions }}</div>
                <div class="label">{{ Str::plural('Question', $totalQuestions) }}</div>
            </div>
            <div class="stat-box">
                <div class="num">{{ $lesson->quizzes->count() }}</div>
                <div class="label">{{ Str::plural('Quiz', $lesson->quizzes->count()) }}</div>
            </div>
        </div>
        <button class="btn-big btn-go" onclick="goToMovie()">🚀 Let's Learn!</button>
    </div>
</div>

{{-- ═══ MOVIE SCREEN ═══ --}}
<div class="screen" id="screen-movie">
    <div class="movie-wrap">
        <div class="movie-card">
            @if ($lesson->video_url)
                @if (str_contains($lesson->video_url, 'youtube') || str_contains($lesson->video_url, 'youtu.be'))
                    @php
                        $ytId = '';
                        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $lesson->video_url, $m)) { $ytId = $m[1]; }
                    @endphp
                    @if ($ytId)
                        <iframe src="https://www.youtube.com/embed/{{ $ytId }}?rel=0" frameborder="0" allowfullscreen></iframe>
                    @else
                        <video controls><source src="{{ $lesson->video_url }}"></video>
                    @endif
                @else
                    <video controls><source src="{{ $lesson->video_url }}"></video>
                @endif
            @else
                <div style="height:450px;display:flex;align-items:center;justify-content:center;color:#64748b;flex-direction:column;">
                    <div style="font-size:64px;">🎬</div>
                    <p style="margin-top:16px;">No video attached to this lesson yet.</p>
                </div>
            @endif
        </div>
        <div class="movie-info">
            <h2>🎬 {{ $lesson->title }}</h2>
            <p>Watch the video, then tap "Start Quiz" to play!</p>
            <div class="movie-actions">
                <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn-exit">← Back</a>
                @if ($totalQuestions > 0)
                    <button class="btn-big btn-next" onclick="goToQuiz()">🧩 Start Quiz →</button>
                @else
                    <button class="btn-big btn-next" onclick="goToResult()" style="opacity:0.6;">Done ✓</button>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ═══ QUIZ SCREEN ═══ --}}
<div class="screen" id="screen-quiz">
    <div class="quiz-card" id="quiz-card">
        {{-- Dynamically rendered by JS --}}
    </div>
</div>

{{-- ═══ RESULT SCREEN ═══ --}}
<div class="screen" id="screen-result">
    <div class="result-card" id="result-card">
        {{-- Dynamically rendered by JS --}}
    </div>
</div>

@php
    $allQuestions = $lesson->quizzes->flatMap(function ($quiz) {
        return $quiz->questions->map(function ($q) {
            return [
                'id' => $q->id,
                'prompt' => $q->prompt,
                'prompt_image_url' => $q->prompt_image_url,
                'quiz_type' => $q->quizType?->name ?? 'Multiple Choice',
                'hint' => $q->hint,
                'options' => $q->options->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'text' => $o->text_value,
                        'image' => $o->image_url,
                        'is_correct' => (bool) $o->is_correct,
                    ];
                })->values(),
            ];
        });
    })->values();
@endphp
<script>
// ═══ State ═══
const allQuestions = @json($allQuestions);

let currentQ = 0;
let score = 0;
let answered = false;

// ═══ Navigation ═══
function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
function goToMovie() { showScreen('screen-movie'); }
function goToQuiz() { currentQ = 0; score = 0; renderQuestion(); showScreen('screen-quiz'); }
function goToResult() { renderResult(); showScreen('screen-result'); }

// ═══ Quiz Rendering ═══
function renderQuestion() {
    answered = false;
    const q = allQuestions[currentQ];
    if (!q) return goToResult();

    const progressPct = ((currentQ) / allQuestions.length) * 100;

    let html = `
        <div class="quiz-progress">
            <span class="q-num">Question ${currentQ + 1} of ${allQuestions.length}</span>
            <div class="bar"><div class="fill" style="width:${progressPct}%"></div></div>
            <span class="q-num">⭐ ${score}/${allQuestions.length}</span>
        </div>
        <div style="text-align:center;">
            <span class="quiz-type-badge">${q.quiz_type}</span>
        </div>
        <div class="quiz-prompt">${q.prompt}</div>
        ${q.prompt_image_url ? `<img class="quiz-prompt-img" src="${q.prompt_image_url}">` : ''}
        ${q.hint ? `<div class="quiz-hint">💡 ${q.hint}</div>` : ''}
        <div class="options-grid" id="options-grid">
    `;

    q.options.forEach((opt, i) => {
        const content = opt.image 
            ? `<img src="${opt.image}">${opt.text || ''}`
            : opt.text || `Option ${i+1}`;
        html += `<button class="option-btn" data-idx="${i}" onclick="selectAnswer(${i})">${content}</button>`;
    });

    html += `</div>
        <div class="feedback" id="feedback"></div>
        <div class="quiz-actions" id="quiz-actions" style="display:none;">
            ${currentQ < allQuestions.length - 1 
                ? `<button class="btn-big btn-next" onclick="nextQuestion()">Next →</button>`
                : `<button class="btn-big btn-go" onclick="goToResult()">See Results! 🎉</button>`
            }
        </div>
    `;

    document.getElementById('quiz-card').innerHTML = html;
}

function selectAnswer(idx) {
    if (answered) return;
    answered = true;

    const q = allQuestions[currentQ];
    const buttons = document.querySelectorAll('.option-btn');
    const feedback = document.getElementById('feedback');
    const actions = document.getElementById('quiz-actions');

    const selected = q.options[idx];
    const isCorrect = selected.is_correct;

    buttons.forEach((btn, i) => {
        btn.style.pointerEvents = 'none';
        if (q.options[i].is_correct) {
            btn.classList.add('correct');
        } else if (i === idx && !isCorrect) {
            btn.classList.add('wrong');
        }
    });

    if (isCorrect) {
        score++;
        feedback.className = 'feedback correct';
        feedback.textContent = '🎉 Correct! Great job!';
    } else {
        feedback.className = 'feedback wrong';
        feedback.textContent = '😅 Oops! The right answer is highlighted.';
    }

    actions.style.display = 'flex';
}

function nextQuestion() {
    currentQ++;
    renderQuestion();
}

// ═══ Result Rendering ═══
function renderResult() {
    const total = allQuestions.length;
    const pct = total > 0 ? Math.round((score / total) * 100) : 0;
    
    let emoji, msg, stars, borderColor;
    if (pct >= 90) { emoji = '🏆'; msg = "Amazing! You're a superstar!"; stars = '⭐⭐⭐'; borderColor = '#fbbf24'; }
    else if (pct >= 70) { emoji = '🎉'; msg = 'Great job! You did it!'; stars = '⭐⭐⭐'; borderColor = '#22c55e'; }
    else if (pct >= 50) { emoji = '😊'; msg = 'Good try! Keep practicing!'; stars = '⭐⭐'; borderColor = '#3b82f6'; }
    else { emoji = '💪'; msg = "Don't give up! Try again!"; stars = '⭐'; borderColor = '#a78bfa'; }

    let html = `
        <div class="result-emoji">${emoji}</div>
        <h1 style="color:#1e293b;margin-bottom:8px;">Quiz Complete!</h1>
        <div class="result-stars">${stars}</div>
        <div class="score-circle" style="border-color:${borderColor};color:${borderColor};">
            <div class="score-num">${score}/${total}</div>
            <div class="score-pct">${pct}%</div>
        </div>
        <div class="result-msg">${msg}</div>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <button class="btn-big btn-go" onclick="goToQuiz()">🔄 Play Again</button>
            <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn-exit" style="display:inline-block;line-height:40px;">← Back to Admin</a>
        </div>
        <div style="margin-top:20px;font-size:12px;color:#94a3b8;">👁️ Preview mode — no score was saved.</div>
    `;

    document.getElementById('result-card').innerHTML = html;
}
</script>
</body>
</html>