@extends('kids.layouts.app')

@section('title', "{$mission->title} — Mission Time!")

@push('kid-styles')
<style>
/* premium_style.css - The new UI overhaul based on mockups */
:root {
    --kid-bg: #FFF9E6;
    --kid-primary: #FFB347;
    --kid-text: #5A3E36;
    --kid-text-muted: #8C6F66;
    --kid-bubble-bg: #FFFFFF;
    
    --btn-blue: #60A5FA;
    --btn-blue-dark: #3B82F6;
    --btn-green: #34D399;
    --btn-green-dark: #10B981;
    --btn-red: #F87171;
    --btn-red-dark: #EF4444;
    --btn-yellow: #FCD34D;
    --btn-yellow-dark: #F59E0B;
}

body {
    background-color: var(--kid-bg);
    /* Subtle background pattern - using radial gradients for dots and basic shapes */
    background-image: radial-gradient(#FDE68A 10%, transparent 11%), radial-gradient(#FDE68A 10%, transparent 11%);
    background-size: 60px 60px;
    background-position: 0 0, 30px 30px;
    color: var(--kid-text);
    font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    overflow-x: hidden;
}

/* Hide that intrusive tooltip immediately */
.idle-hint {
    display: none !important;
}

.quiz-stage {
    max-width: 900px;
    margin: 0 auto;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    position: relative;
    padding-bottom: 40px;
}

/* HEADER */
.quiz-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    z-index: 20;
}

.quiz-header .exit-btn, .quiz-header .settings-btn {
    text-decoration: none;
    font-size: 28px;
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 50%;
    box-shadow: 0 4px 0 rgba(0,0,0,0.05);
    color: var(--kid-text);
}

.quiz-header .progress-wrap {
    flex-grow: 1;
    margin: 0 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.quiz-header .progress-bar-bg {
    width: 100%;
    max-width: 400px;
    height: 16px;
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
}

.quiz-header .progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--btn-green), var(--btn-yellow), var(--btn-red));
    border-radius: 20px;
    transition: width 0.5s ease;
}

/* LEO ZONE (Centered) */
.leo-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
    margin-bottom: 24px;
    z-index: 10;
}

.leo-mascot {
    font-size: 80px;
    line-height: 1;
    filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    margin-bottom: 12px;
}

.leo-bubble {
    background: var(--kid-bubble-bg);
    color: var(--kid-text);
    padding: 16px 32px;
    border-radius: 24px;
    font-size: 22px;
    font-weight: 800;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    position: relative;
    text-align: center;
    max-width: 80%;
}

.leo-bubble::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 0 12px 12px 12px;
    border-style: solid;
    border-color: transparent transparent var(--kid-bubble-bg) transparent;
}

/* QUESTION ZONE */
.question-zone {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 20px;
    margin-bottom: 30px;
}

.question-image {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
    /* REMOVED checkerboard class/styles. Let it sit naturally. */
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.1));
}

/* LANDSCAPE RESPONSIVE SPLIT */
@media (min-width: 768px) and (orientation: landscape) {
    .quiz-landscape-split {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 40px;
        margin-top: 20px;
    }
    .leo-zone {
        flex: 1;
        margin: 0;
        align-items: flex-end;
    }
    .leo-bubble {
        margin-top: 16px;
    }
    .leo-bubble::before {
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        border-width: 0 12px 12px 12px;
        border-color: transparent transparent var(--kid-bubble-bg) transparent;
    }
    .question-zone {
        flex: 1;
        align-items: flex-start;
        margin: 0;
        padding: 0;
    }
}

/* CHUNKY BUTTONS (Answer Cards & Puzzle Slots) */
.answer-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 16px;
    padding: 0 20px;
}

.answer-card {
    background: white;
    border: 4px solid #E5E7EB;
    border-bottom-width: 8px; /* Chunky 3D effect */
    border-radius: 20px;
    padding: 20px;
    min-width: 100px;
    min-height: 100px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    font-weight: 900;
    color: var(--kid-text);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}

.answer-card:hover {
    transform: translateY(-2px);
}

.answer-card:active {
    transform: translateY(4px);
    border-bottom-width: 4px;
    margin-bottom: 4px; /* offset the border change */
}

/* Coloring specific cards based on content or state */
.answer-card.color-blue { border-color: var(--btn-blue-dark); color: var(--btn-blue-dark); }
.answer-card.color-green { border-color: var(--btn-green-dark); color: var(--btn-green-dark); }
.answer-card.color-red { border-color: var(--btn-red-dark); color: var(--btn-red-dark); }
.answer-card.color-yellow { border-color: var(--btn-yellow-dark); color: var(--btn-yellow-dark); }

.answer-card.correct {
    border-color: var(--btn-green-dark);
    background: #D1FAE5;
    color: var(--btn-green-dark);
    animation: kid-pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.answer-card.incorrect {
    border-color: #E5E7EB;
    background: #F3F4F6;
    color: #9CA3AF;
    opacity: 0.7;
    animation: gentle-shake 0.4s ease;
}

/* FILL IN THE BLANK (Puzzle Slots) */
.fill-blank-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 30px;
}

.puzzle-word {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}

.puzzle-slot {
    width: 60px;
    height: 70px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 900;
    border-radius: 12px;
    text-transform: uppercase;
}

.puzzle-slot.filled {
    color: var(--kid-text);
    background: transparent;
}

.puzzle-slot.blank {
    background: white;
    border: 3px dashed #CBD5E1;
    color: transparent;
}

.puzzle-slot.blank.revealed {
    background: transparent;
    border: none;
    color: var(--kid-text);
    border-bottom: 4px solid var(--kid-text);
    animation: kid-pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.puzzle-options {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.puzzle-option {
    width: 70px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 4px solid #E5E7EB;
    border-bottom-width: 8px;
    border-radius: 16px;
    font-size: 36px;
    font-weight: 900;
    color: var(--kid-text);
    cursor: pointer;
    transition: all 0.15s ease;
}

.puzzle-option:active {
    transform: translateY(4px);
    border-bottom-width: 4px;
}

/* MATCHING BOARD */
.matching-board {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    max-width: 400px;
    margin: 0 auto;
    padding: 0 20px;
}

.matching-item {
    background: white;
    border: 4px solid #E5E7EB;
    border-bottom-width: 8px;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    aspect-ratio: 1; /* Make them squares */
}

.matching-item:active {
    transform: translateY(4px);
    border-bottom-width: 4px;
}

.matching-item.selected {
    border-color: var(--btn-blue-dark);
    background: #EFF6FF;
    transform: translateY(2px);
    border-bottom-width: 6px;
}

.matching-item.matched {
    border-color: var(--btn-green-dark);
    background: #D1FAE5;
    opacity: 0.6;
    pointer-events: none;
}

/* Animations */
@keyframes kid-pop {
    0% { transform: scale(0.8); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}
@keyframes gentle-shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}


/* Colored borders for puzzle options and answer cards */
.puzzle-option:nth-child(3n+1), .answer-card:nth-child(3n+1) { border-color: var(--btn-green); color: var(--btn-green-dark); }
.puzzle-option:nth-child(3n+2), .answer-card:nth-child(3n+2) { border-color: var(--btn-blue); color: var(--btn-blue-dark); }
.puzzle-option:nth-child(3n+3), .answer-card:nth-child(3n+3) { border-color: var(--btn-red); color: var(--btn-red-dark); }
.puzzle-option:nth-child(4n), .answer-card:nth-child(4n) { border-color: var(--btn-yellow); color: var(--btn-yellow-dark); }

/* Remove default background if any */
.puzzle-option, .answer-card { background: white; }

</style>
@endpush

@section('kid-content')

@php
    // Build questions JSON for Alpine.js — REAL data from database
    $questionsJson = $mission->questions->map(function ($q) {
        // Normalize slug: DB uses hyphens (multiple-choice), JS uses underscores (multiple_choice)
        $rawSlug = $q->quizType ? $q->quizType->slug : 'multiple-choice';
        $typeSlug = str_replace('-', '_', $rawSlug);
        $typeName = $q->quizType ? $q->quizType->name : 'Question';
        $typeIcon = $q->quizType ? ($q->quizType->icon ?? '❓') : '❓';

        // Resolve narration audio (if narration_id is set)
        $narrationAudioUrl = null;
        if ($q->narration && $q->narration->has_audio) {
            $narrationAudioUrl = $q->narration->audio_url;
        }

        return [
            'id' => $q->id,
            'prompt' => $q->prompt,
            'hint' => $q->hint,
            'explanation' => $q->explanation,
            'image' => $q->prompt_image_url,
            // Audio: prefer direct prompt_audio_url, fall back to narration audio
            'audio' => $q->prompt_audio_url ?: $narrationAudioUrl,
            'type' => $typeSlug,
            'typeName' => $typeName,
            'typeIcon' => $typeIcon,
            'points' => $q->points,
            'metadata' => $q->metadata,
            'options' => $q->options->map(function ($opt) {
                return [
                    'id' => $opt->id,
                    'text' => $opt->text_value,
                    'image' => $opt->image_url,
                    'audio' => $opt->audio_url,
                    'is_correct' => (bool) $opt->is_correct,
                    'content_type' => $opt->content_type,
                    'match_key' => $opt->match_key,
                ];
            })->values()->toArray(),
        ];
    })->values()->toArray();
@endphp

<script>
    window.__quizConfig = {
        questions: @json($questionsJson),
        childStars: {{ (int)($child->total_stars ?? 0) }},
        quizId: {{ (int)$mission->id }},
        voiceProfile: @json($mission->intro_voice_profile),
        submitUrl: @json(route('kids.mission.submit', [$world, $mission])),
        exitUrl: @json($world ? route('kids.mission-video', [$world, $mission]) : route('kids.map')),
        csrfToken: @json(csrf_token()),
    };
</script>
<div class="quiz-stage" x-data="quizEngine(window.__quizConfig)" x-init="init()" @click="if(window.KidSoundLayer) window.KidSoundLayer.init()">

    {{-- Loading state (shown until Alpine initializes) --}}
    <div x-show="!initialized" style="display:flex;align-items:center;justify-content:center;min-height:100vh;flex-direction:column;gap:16px;">
        <div style="font-size:64px;animation:kid-wiggle 2s ease-in-out infinite;">🦁</div>
        <p style="font-family:var(--kid-font-heading);font-size:24px;font-weight:900;color:var(--kid-text);">Loading Quiz...</p>
        <p style="font-size:16px;color:var(--kid-text-muted);">Getting ready for fun!</p>
    </div>

    {{-- HEADER --}}
    <div class="quiz-header" x-show="initialized" x-cloak>
        <a href="#" class="exit-btn" @click.prevent="exitQuiz()" aria-label="Exit quiz">🏠</a>
        <div class="progress-wrap">
            <div class="progress-bar-bg">
                <div class="progress-bar-fill" :style="`width: ${progressPercent}%`"></div>
            </div>
            <div class="progress-label" x-text="`Question ${currentIndex + 1} of ${questions.length}`"></div>
        </div>
        <div class="star-pill" :class="{ pulse: starPulse }">
            <span style="font-size:20px;">⭐</span>
            <span x-text="totalStars"></span>
        </div>
        <a href="#" class="settings-btn" aria-label="Settings" style="margin-left: 12px;">⚙️</a>
    </div>

    {{-- MAIN QUIZ AREA (hidden when complete) --}}
    <template x-if="!quizComplete">
        <div>
            {{-- LEO + QUESTION (side-by-side in landscape) --}}
            <div class="quiz-landscape-split">
                {{-- LEO ZONE --}}
                <div class="leo-zone" x-show="leoMessage">
                    <div class="leo-mascot" :class="{ celebrating: leoCelebrating }">🦁</div>
                    <div class="leo-bubble" x-html="leoMessage.replace(/\n/g, '<br>')"></div>
                </div>

                {{-- QUESTION --}}
                <div class="question-zone" :key="'q-' + currentIndex">
                <div class="question-badge" x-text="`${currentQuestion.typeIcon} ${currentQuestion.typeName}`"></div>
                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: center; flex-wrap: wrap; margin-bottom: 16px;">
                    <button @click="playQuestionAudio()" 
                            class="kid-btn" 
                            style="width: 48px; height: 48px; border-radius: 50%; background: var(--kid-primary); color: white; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 4px 0 rgba(0,0,0,0.15);"
                            aria-label="Replay Audio">
                        🔊
                    </button>
                    <div class="question-prompt" x-text="currentQuestion.prompt" style="margin: 0;"></div>
                </div>
                <template x-if="currentQuestion.image">
                    <img :src="currentQuestion.image" alt="Question image" class="question-image">
                </template>
                <template x-if="currentQuestion.hint && showHint">
                    <p style="color: var(--kid-text-muted); font-size: 16px; margin-top: 8px;">
                        💡 <span x-text="currentQuestion.hint"></span>
                    </p>
                </template>
                </div>
            </div>

            {{-- ANSWERS --}}
            <div class="answer-zone" :key="'a-' + currentIndex">

                {{-- Multiple Choice / Tap Answer / True False / Listen Choose --}}
                                <template x-if="['multiple_choice','tap_answer','true_false','listen_choose'].includes(currentQuestion.type)">
                    <!-- Determine layout: if ANY option has a big image but short text (like 1,2,3), or if we explicitly want vertical, let's guess based on text length or image presence -->
                    <div class="answer-grid" :class="currentQuestion.options.some(o => o.image) ? 'layout-vertical' : 'layout-square'" :data-count="currentQuestion.options.length">
                        <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                            <div class="answer-card"
                                 :class="getCardClass(i, option.is_correct)"
                                 :style="\`animation-delay: ${0.1 + i * 0.08}s\`"
                                 @click="selectOption(i)"
                                 :aria-label="\`Answer: ${option.text}\`"
                                 role="button" tabindex="0"
                                 @keydown.enter="selectOption(i)"
                                 @keydown.space.prevent="selectOption(i)">
                                
                                <template x-if="currentQuestion.options.some(o => o.image)">
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                        <div class="card-index" x-text="i + 1" :style="'color: ' + (i%4===0 ? 'var(--btn-green-dark)' : i%4===1 ? 'var(--btn-blue-dark)' : i%4===2 ? 'var(--btn-red-dark)' : 'var(--btn-yellow-dark)')"></div>
                                        <template x-if="option.image">
                                            <img :src="option.image" :alt="option.text" x-on:error="option.image = null" class="card-image">
                                        </template>
                                        <template x-if="!option.image && option.text">
                                            <span x-text="option.text" style="font-size:32px;"></span>
                                        </template>
                                    </div>
                                </template>

                                <template x-if="!currentQuestion.options.some(o => o.image)">
                                    <div style="display: flex; flex-direction: column; align-items: center;">
                                        <span x-text="option.text"></span>
                                    </div>
                                </template>

                                <template x-if="answered && selectedIndex === i && option.is_correct">
                                    <span class="badge" style="position:absolute; top: 10px; right: 10px; font-size: 24px;">✅</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
                                <template x-if="!option.image && option.text && option.text.trim() !== ''">
                                    <span x-text="option.text"></span>
                                </template>
                                <template x-if="answered && selectedIndex === i && option.is_correct">
                                    <span class="badge">✅</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- Flashcard --}}
                <template x-if="currentQuestion.type === 'flashcard'">
                    <div x-data="{ flipped: false }" style="max-width:540px;margin:0 auto;">
                        <div class="flashcard-container" style="perspective: 1000px; margin-bottom: 24px;">
                            <div class="flashcard-answer" 
                                 @click="flipped = !flipped"
                                 :style="flipped ? 'transform: rotateY(180deg);' : ''"
                                 style="transition: transform 0.6s; transform-style: preserve-3d; cursor: pointer; position: relative; height: 280px; background: white; border: 4px solid #EDE9FE; border-radius: var(--kid-radius-lg); box-shadow: var(--kid-shadow-soft); display: flex; align-items: center; justify-content: center; margin-bottom: 0; padding: 0;">
                                
                                <!-- Front -->
                                <div style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px;">
                                    <template x-if="currentQuestion.options.find(o => o.is_correct)?.image || currentQuestion.image">
                                        <img :src="currentQuestion.options.find(o => o.is_correct)?.image || currentQuestion.image" style="max-width: 100%; max-height: 180px; object-fit: contain;">
                                    </template>
                                    <template x-if="!(currentQuestion.options.find(o => o.is_correct)?.image || currentQuestion.image)">
                                        <div class="card-icon" style="font-size: 80px;">❓</div>
                                    </template>
                                    <div style="margin-top: auto; color: var(--kid-text-muted); font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">Tap to flip</div>
                                </div>

                                <!-- Back -->
                                <div style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; transform: rotateY(180deg); display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, #EDE9FE, #FCE7F3); border-radius: calc(var(--kid-radius-lg) - 4px);">
                                    <div class="card-word" x-text="currentQuestion.options.find(o => o.is_correct)?.text || '?'" style="font-size: 48px; font-family: var(--kid-font-heading); font-weight: 900; color: var(--kid-text);"></div>
                                    <button @click.stop="if(window.KidSoundLayer) window.KidSoundLayer.playAudio(currentQuestion.options.find(o => o.is_correct)?.audio || currentQuestion.audio)" 
                                            class="kid-btn" 
                                            style="margin-top: 24px; width: 48px; height: 48px; border-radius: 50%; background: var(--kid-primary); color: white; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 4px 0 rgba(0,0,0,0.15);"
                                            x-show="currentQuestion.options.find(o => o.is_correct)?.audio || currentQuestion.audio">
                                        🔊
                                    </button>
                                </div>
                            </div>
                        </div>

                        <p style="text-align:center;color:var(--kid-text-muted);margin-bottom:16px; font-weight: bold;">
                            Did you know the answer?
                        </p>
                        <div class="flashcard-buttons">
                            <div class="answer-card"
                                 :class="answered && selectedIndex === 0 ? 'incorrect' : ''"
                                 style="background:#FEE2E2;border-color:#FCA5A5;color:#DC2626;"
                                 @click="selectFlashcard(false)">
                                ❌ No
                            </div>
                            <div class="answer-card"
                                 :class="answered && selectedIndex === 1 ? 'correct' : ''"
                                 style="background:#DCFCE7;border-color:#86EFAC;color:#16A34A;"
                                 @click="selectFlashcard(true)">
                                ✅ Yes!
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Fill in the Blank --}}
                <template x-if="currentQuestion.type === 'fill_blank'">
                    <div class="fill-blank-container">
                        <div class="puzzle-word">
                            <template x-for="(char, idx) in (currentQuestion.metadata?.puzzle || [])" :key="idx">
                                <div class="puzzle-slot" 
                                     :class="char === '_' ? (answered ? 'blank revealed' : 'blank') : 'filled'">
                                    <span x-text="char === '_' ? (answered ? currentQuestion.options.find(o => o.is_correct)?.text : '?') : char"></span>
                                </div>
                            </template>
                        </div>
                        <div class="puzzle-options">
                            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                <div class="puzzle-option"
                                     :class="answered && selectedIndex === i && !option.is_correct ? 'wrong' : ''"
                                     @click="selectOption(i)">
                                    <span x-text="option.text"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Matching (QT-03) --}}
                <template x-if="currentQuestion.type === 'matching'">
                    <div>
                        <div class="matching-progress">
                            <span class="check" x-text="matchedPairs.length"></span>
                            / <span x-text="matchLeftItems.length"></span> pairs matched!
                        </div>
                        <div class="matching-board">
                            <div class="matching-column">
                                <template x-for="(option, i) in matchLeftItems" :key="'L-' + option.id">
                                    <div class="matching-item"
                                         :class="getMatchItemClass('left', i)"
                                         @click="selectMatch('left', i)">
                                        <template x-if="option.image">
                                            <img :src="option.image" x-on:error="option.image = null" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">
                                        </template>
                                        <template x-if="!option.image && option.text && option.text.trim() !== ''">
                                            <span x-text="option.text"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="matching-column">
                                <template x-for="(item, j) in matchRightItems" :key="'R-' + item.originalIndex">
                                    <div class="matching-item"
                                         :class="getMatchItemClass('right', j)"
                                         @click="selectMatch('right', j)">
                                        <template x-if="item.image">
                                            <img :src="item.image" x-on:error="item.image = null" style="max-width: 100%; max-height: 100px; border-radius: 8px; margin-bottom: 4px;" alt="">
                                        </template>
                                        <template x-if="!item.image && item.text && item.text.trim() !== ''">
                                            <span x-text="item.text"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Sequence / Drag & Drop Order (QT-05) --}}
                <template x-if="currentQuestion.type === 'drag_sequence'">
                    <div class="sequence-board">
                        <div class="sequence-slots-label">📋 Put them in order here:</div>
                        <div class="sequence-slots">
                            <template x-for="(slot, i) in seqSlots" :key="'slot-' + i">
                                <div class="sequence-slot"
                                     :class="getSeqSlotClass(i)"
                                     @click="selectSeqSlot(i)">
                                    <span class="slot-label" x-text="`${i + 1}${i === 0 ? 'st' : i === 1 ? 'nd' : i === 2 ? 'rd' : 'th'}`"></span>
                                    <template x-if="slot !== null">
                                        <div class="slot-number" style="display:flex; justify-content:center; align-items:center; width:100%; height:100%;">
                                            <template x-if="seqCards[slot].image">
                                                <img :src="seqCards[slot].image" alt="" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">
                                            </template>
                                            <template x-if="!seqCards[slot].image && seqCards[slot].text && seqCards[slot].text.trim() !== ''">
                                                <span x-text="seqCards[slot].text"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="slot === null">
                                        <span class="slot-placeholder">⬇️</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="sequence-tray-label">🃏 Drag or tap a number, then tap a slot:</div>
                        <div class="sequence-tray">
                            <template x-for="(card, i) in seqCards" :key="'card-' + i">
                                <div class="sequence-card"
                                     :class="getSeqCardClass(i)"
                                     @click="selectSeqCard(i)"
                                     style="display:flex; justify-content:center; align-items:center;">
                                    <template x-if="card.image">
                                        <img :src="card.image" alt="" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">
                                    </template>
                                    <template x-if="!card.image && card.text && card.text.trim() !== ''">
                                        <span x-text="card.text"></span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <button class="sequence-check-btn"
                                :disabled="seqSlots.includes(null)"
                                @click="checkSequence()">
                            ✅ Check My Answer!
                        </button>
                    </div>
                </template>

                {{-- Count Objects (QT-09) --}}
                <template x-if="currentQuestion.type === 'count_objects'">
                    <div>
                        <div class="count-objects-display" x-show="(currentQuestion.metadata?.objects || []).length > 0">
                            <div class="count-objects-emoji">
                                <template x-for="(emoji, i) in (currentQuestion.metadata?.objects || [])" :key="i">
                                    <span class="count-emoji" x-text="emoji"></span>
                                </template>
                            </div>
                        </div>
                        <div class="answer-grid" :class="{'long-text-mode': currentQuestion.options.some(o => o.text && o.text.length > 15)}" :data-count="currentQuestion.options.length">
                            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                <div class="answer-card"
                                     :class="getCardClass(i, option.is_correct)"
                                     :style="`animation-delay: ${0.1 + i * 0.08}s`"
                                     @click="selectOption(i)"
                                     :aria-label="`Answer: ${option.text}`"
                                     role="button" tabindex="0"
                                     @keydown.enter="selectOption(i)"
                                     @keydown.space.prevent="selectOption(i)">
                                    <template x-if="option.image">
                                        <img :src="option.image" :alt="option.text" x-on:error="option.image = null" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">
                                    </template>
                                    <template x-if="!option.image && option.text && option.text.trim() !== ''">
                                        <span x-text="option.text"></span>
                                    </template>
                                    <template x-if="answered && selectedIndex === i && option.is_correct">
                                        <span class="badge">✅</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
                {{-- Complete the Pattern (QT-10) --}}
                <template x-if="currentQuestion.type === 'complete_pattern'">
                    <div class="pattern-board">
                        {{-- Pattern strip: shows sequence tiles + missing "?" slot --}}
                        <div class="pattern-strip" x-show="(currentQuestion.metadata?.sequence || []).length > 0">
                            <template x-for="(item, i) in (currentQuestion.metadata?.sequence || [])" :key="'seq-' + i">
                                <div class="pattern-tile"
                                     :style="`animation-delay: ${0.1 + i * 0.1}s`">
                                    <span x-text="item"></span>
                                </div>
                            </template>
                            {{-- The missing slot — shows "?" until answered, then reveals correct answer --}}
                            <div class="pattern-tile missing"
                                 :class="answered ? 'revealed' : ''"
                                 :style="`animation-delay: ${0.1 + (currentQuestion.metadata?.sequence?.length || 0) * 0.1}s`">
                                <template x-if="!answered">
                                    <span class="question-mark">?</span>
                                </template>
                                <template x-if="answered">
                                    <span x-text="getCorrectAnswerText()"></span>
                                </template>
                            </div>
                        </div>

                        {{-- Arrow pointing down to answers --}}
                        <div style="text-align:center;margin-bottom:var(--kid-space-3);">
                            <span class="pattern-arrow" x-show="(currentQuestion.metadata?.sequence || []).length > 0">⬇️</span>
                        </div>

                        {{-- Answer choices --}}
                        <div class="pattern-answers-label">Tap your answer!</div>
                        <div class="pattern-answer-grid">
                            <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                                <div class="answer-card"
                                     :class="getCardClass(i, option.is_correct)"
                                     :style="`animation-delay: ${0.1 + i * 0.08}s`"
                                     @click="selectOption(i)"
                                     :aria-label="`Answer: ${option.text}`"
                                     role="button" tabindex="0"
                                     @keydown.enter="selectOption(i)"
                                     @keydown.space.prevent="selectOption(i)">
                                    <template x-if="option.image">
                                        <img :src="option.image" :alt="option.text" x-on:error="option.image = null" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">
                                    </template>
                                    <template x-if="!option.image && option.text && option.text.trim() !== ''">
                                        <span x-text="option.text"></span>
                                    </template>
                                    <template x-if="answered && selectedIndex === i && option.is_correct">
                                        <span class="badge">✅</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Memory Match (QT-11) --}}
                <template x-if="currentQuestion.type === 'memory_match'">
                    <div class="memory-board">
                        <div class="memory-progress">
                            <span class="check" x-text="memoryMatchedPairs"></span>
                            / <span x-text="memoryTotalPairs"></span> pairs found! 🎉
                        </div>
                        <div class="memory-grid">
                            <template x-for="(card, i) in memoryCards" :key="'mem-' + i">
                                <div class="memory-card"
                                     :class="getMemoryCardClass(i)"
                                     @click="flipMemoryCard(i)">
                                    <div class="memory-card-inner">
                                        <div class="memory-card-face memory-card-back">🃏</div>
                                        <div class="memory-card-face memory-card-front" x-text="card.text"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Speak & Repeat (QT-07) --}}
                <template x-if="currentQuestion.type === 'speak_repeat'">
                    <div class="speak-board">
                        {{-- Word card: shows the word/phrase to repeat --}}
                        <div class="speak-word-card">
                            <div class="speak-word-emoji" x-text="currentQuestion.metadata?.emoji || '🗣️'"></div>
                            <div class="speak-word-text" x-text="currentQuestion.metadata?.word || currentQuestion.options.find(o => o.is_correct)?.text || '—'"></div>
                        </div>

                        {{-- 🔊 HEAR THE WORD button — plays the word out loud so the child can listen --}}
                        <button class="listen-audio-btn"
                                :class="{ playing: isSpeaking }"
                                @click="playTargetWord()"
                                :disabled="speakCompleted"
                                style="width: 120px; height: 120px; margin-bottom: var(--kid-space-4);">
                            <span class="sound-waves"></span>
                            <span class="sound-waves" style="animation-delay: 0.3s;"></span>
                            <span class="speaker-icon" x-text="isSpeaking ? '🔊' : '👂'"></span>
                            <span class="speaker-label" x-text="isSpeaking ? 'Playing...' : 'HEAR IT'"></span>
                        </button>

                        {{-- Mic button — HOLD TO RECORD (press = start, release = stop & check) --}}
                        <button class="speak-mic-btn"
                                :class="{ listening: speakListening }"
                                @mousedown.prevent="startSpeakHold()"
                                @mouseup.prevent="endSpeakHold()"
                                @mouseleave.prevent="endSpeakHold()"
                                @touchstart.prevent="startSpeakHold()"
                                @touchend.prevent="endSpeakHold()"
                                @touchcancel.prevent="endSpeakHold()"
                                @contextmenu.prevent=""
                                :disabled="speakCompleted"
                                :aria-label="speakListening ? 'Listening... Release to check!' : 'Hold to speak!'">
                            <span class="mic-waves"></span>
                            <span class="mic-waves" style="animation-delay: 0.3s;"></span>
                            <span class="mic-icon" x-text="speakListening ? '🔴' : '🎤'"></span>
                            <span class="mic-label" x-text="speakListening ? 'LISTENING...' : 'HOLD TO SPEAK!'"></span>
                        </button>
                        <p style="font-family:var(--kid-font-heading);font-weight:700;font-size:14px;color:var(--kid-text-muted);margin-top:-12px;margin-bottom:var(--kid-space-3);">
                            ✋ Press & hold the mic, say the word, then let go!
                        </p>

                        {{-- Status message --}}
                        <div class="speak-status" x-text="speakStatus"></div>

                        {{-- Attempt dots (3 tries) --}}
                        <div class="speak-attempts">
                            <template x-for="i in 3" :key="'dot-' + i">
                                <div class="speak-attempt-dot" :class="{ active: speakDots[i-1] }"></div>
                            </template>
                        </div>

                        {{-- Skip button (appears after first attempt) --}}
                        <div class="speak-actions">
                            <button class="speak-skip-btn"
                                    :class="{ visible: speakAttempts >= 1 && !speakCompleted }"
                                    @click="skipSpeak()">
                                ⏭️ Skip
                            </button>
                        </div>
                    </div>
                </template>

                {{-- TRACING (QT-12) — practice activity (teach only, no scoring) --}}
                <template x-if="currentQuestion.type === 'tracing'">
                    <div class="tracing-board">
                        <div class="tracing-info">
                            <span class="tracing-label" x-text="currentQuestion.metadata?.traceType || 'Letter'"></span>
                        </div>
                        <div class="tracing-canvas-wrap">
                            <canvas class="tracing-canvas"
                                    width="300" height="300"
                                    :style="currentQuestion.image ? `background-image: url('${currentQuestion.image}'); background-size: contain; background-position: center; background-repeat: no-repeat;` : ''"
                                    :key="'trace-' + currentQuestion.id"
                                    x-init="initTracingCanvas($el)"></canvas>
                        </div>
                        <div class="tracing-status" x-text="tracingStatus"></div>
                        <div class="tracing-actions">
                            <button class="tracing-demo-btn"
                                    x-show="!answered && !tracingDemoPlaying && !currentQuestion.image"
                                    @click="playTracingDemo()">
                                ▶️ Watch How!
                            </button>
                            <button class="tracing-clear-btn"
                                    x-show="tracingStrokes > 0 && !answered && !tracingDemoPlaying"
                                    @click="clearTracing()">
                                🔄 Try Again
                            </button>
                            <button class="tracing-done-btn"
                                    x-show="!answered && !tracingDemoPlaying"
                                    @click="doneTracing()">
                                ✏️ I’m Done!
                            </button>
                        </div>
                    </div>
                </template>

                                {{-- Drag & Sort (QT-04) --}}
                <template x-if="currentQuestion.type === 'drag_sort'">
                    <div class="sort-board">
                        {{-- Item tray (unsorted chips) --}}
                        <div class="sequence-tray-label">👇 Tap an item, then tap a box!</div>
                        <div class="sort-tray">
                            <template x-for="(chip, i) in sortChips" :key="'chip-' + i">
                                <div class="sort-chip"
                                     :class="getSortChipClass(i)"
                                     @click="selectSortChip(i)"
                                     x-show="chip.bucket === null"
                                     style="display:flex; justify-content:center; align-items:center;">
                                    <template x-if="chip.image">
                                        <img :src="chip.image" alt="" x-on:error="chip.image = null" style="max-width: 100%; max-height: 80px; object-fit: contain; border-radius: 8px;">
                                    </template>
                                    <template x-if="!chip.image && chip.text && chip.text.trim() !== ''">
                                        <span x-text="chip.text"></span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Category buckets --}}
                        <div class="sort-buckets">
                            <template x-for="(cat, bi) in sortCategories" :key="'bucket-' + bi">
                                <div class="sort-bucket"
                                     :class="getSortBucketClass(bi)"
                                     @click="selectSortBucket(bi)">
                                    <div class="sort-bucket-label" x-text="cat"></div>
                                    <div class="sort-bucket-items">
                                        <template x-for="(chip, ci) in sortChips" :key="'bucket-chip-' + ci">
                                            <div class="sort-chip in-bucket"
                                                 :class="getSortChipInBucketClass(ci)"
                                                 x-show="chip.bucket === cat"
                                                 style="display:flex; justify-content:center; align-items:center;">
                                                <template x-if="chip.image">
                                                    <img :src="chip.image" alt="" x-on:error="chip.image = null" style="max-width: 100%; max-height: 60px; object-fit: contain; border-radius: 8px;">
                                                </template>
                                                <template x-if="!chip.image && chip.text && chip.text.trim() !== ''">
                                                    <span x-text="chip.text"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- Fallback (same as multiple choice) --}}
                <template x-if="!['multiple_choice','tap_answer','true_false','fill_blank','flashcard','matching','drag_sequence','count_objects','complete_pattern','listen_choose','drag_sort','memory_match','speak_repeat','tracing'].includes(currentQuestion.type)">
                    <div class="answer-grid" :class="{'long-text-mode': currentQuestion.options.some(o => o.text && o.text.length > 15)}" :data-count="currentQuestion.options.length">
                        <template x-for="(option, i) in currentQuestion.options" :key="option.id">
                            <div class="answer-card"
                                 :class="getCardClass(i, option.is_correct)"
                                 @click="selectOption(i)">
                                <template x-if="option.image">
                                    <img :src="option.image" :alt="option.text" x-on:error="option.image = null" class="card-image" style="max-width: 100%; max-height: 80px; object-fit: contain; margin-bottom: 4px; border-radius: 8px;">
                                </template>
                                <template x-if="!option.image && option.text && option.text.trim() !== ''">
                                    <span x-text="option.text"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- EXPLANATION --}}
                <template x-if="answered && currentQuestion.explanation">
                    <div class="explanation-box">
                        <p>💡 <span x-text="currentQuestion.explanation"></span></p>
                    </div>
                </template>
            </div>

            {{-- FOOTER --}}
            <div class="quiz-footer">
                <template x-if="!answered && !showIdleHint">
                    <div class="footer-prompt">
                        <template x-if="currentQuestion.type === 'matching'">
                            <span>👆 Match the pairs!</span>
                        </template>
                        <template x-if="currentQuestion.type !== 'matching'">
                            <span>👆 Tap the right answer!</span>
                        </template>
                    </div>
                </template>
                <template x-if="answered">
                    <button class="next-btn" @click="nextQuestion()">
                        <span x-text="currentIndex + 1 >= questions.length ? '🎉 See My Stars!' : 'Next Question →'"></span>
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- RESULTS SCREEN --}}
    <template x-if="quizComplete">
        <div class="results-screen">
            <div class="results-card">
                <div class="results-trophy">🏆</div>
                <div class="results-score">
                    <span x-text="score"></span> / <span x-text="questions.length"></span>
                </div>
                <div class="results-stars">
                    <template x-for="i in 3" :key="i">
                        <div class="star" :class="i <= starsEarned ? 'earned' : 'empty'"
                             :style="`animation-delay: ${i * 100}ms`">⭐</div>
                    </template>
                </div>
                <div class="results-message" x-text="resultMessage"></div>

                <input type="hidden" name="_token" value="{{ csrf_token() }}" x-ref="csrfToken">
                <button type="button"
                        @click="submitQuiz()"
                        class="next-btn"
                        style="background: linear-gradient(90deg, #22C55E, #7C3AED); width: 100%; justify-content: center;">
                    🎉 Collect My Stars!
                </button>

                <button @click="restartQuiz()" class="footer-prompt"
                        style="margin-top: 16px; text-decoration: underline; cursor: pointer; background: none; border: none;">
                    Play Again
                </button>
            </div>
        </div>
    </template>

    {{-- CELEBRATION OVERLAY --}}
    <div class="celebration-overlay" :class="{ visible: celebrating }" @click="dismissCelebration()">
        <div class="celebration-box" @click.stop>
            <div class="celebration-emoji" x-text="celebrationData.emoji || '🎉'"></div>
            <div class="celebration-title" x-text="celebrationData.title || 'Great!'"></div>
            <div class="celebration-subtitle" x-text="celebrationData.subtitle || ''"></div>
        </div>
    </div>

    {{-- IDLE HINT --}}
    <div class="idle-hint" :class="{ visible: showIdleHint }">
        🦁 <span x-text="idleHintText"></span>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/kid/quiz-event-bus.js') }}"></script>
<script src="{{ asset('js/kid/quiz-sound-layer.js') }}"></script>
<script src="{{ asset('js/kid/quiz-reward-layer.js') }}"></script>
<script>
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
        matchRightItems: [],
        matchWrongPair: null,

        // Sequence (QT-05)
        seqCards: [],      // shuffled cards [{text, correctIndex}]
        seqSlots: [],      // array of indices into seqCards, or null
        seqSelectedCard: null,
        seqAnswered: false,

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

            // 🔊 If this option has audio, play it (tap-to-play)
            // This is important for listen_choose and any type where options
            // have associated audio clips.
            if (option.audio) {
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
            return correct ? correct.text : '?';
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

            // In our database seeding, left items have is_correct=false, right items have is_correct=true
            // Split the options into left and right arrays
            this.matchLeftItems = this.currentQuestion.options.filter(o => o.is_correct === false);
            
            const rightOpts = this.currentQuestion.options.filter(o => o.is_correct === true).map((o, i) => ({
                text: o.text,
                image: o.image,
                matchKey: o.match_key,
                originalIndex: i,
            }));
            
            // If the right items weren't seeded (old logic fallback), use the match_key of the left items
            if (rightOpts.length === 0) {
                this.matchLeftItems = [...this.currentQuestion.options];
                const fallbackRight = this.currentQuestion.options.map((o, i) => ({
                    text: o.match_key || o.text,
                    image: null,
                    matchKey: o.match_key,
                    originalIndex: i,
                }));
                this.matchRightItems = this.shuffleArray([...fallbackRight]);
            } else {
                this.matchRightItems = this.shuffleArray([...rightOpts]);
            }
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

            if (leftOpt.match_key === rightItem.matchKey) {
                // Correct match!
                this.matchedPairs.push({
                    leftIndex: leftIndex,
                    rightIndex: rightIndex,
                    matchKey: leftOpt.match_key,
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

            // Use metadata categories if available, else derive from options' match_key
            if (this.currentQuestion.metadata && this.currentQuestion.metadata.categories && this.currentQuestion.metadata.categories.length > 0) {
                this.sortCategories = this.currentQuestion.metadata.categories;
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
            if (this.sortSelectedChip === null) return;

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
                this.currentQuestion.options.find(o => o.is_correct)?.text ||
                '';

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

            // Extract the target word from metadata or options
            this.speakTargetWord = (
                this.currentQuestion.metadata?.word ||
                this.currentQuestion.options.find(o => o.is_correct)?.text ||
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
                // Try to guess from prompt like "Trace the letter B" or "Trace number 5"
                const promptMatch = this.currentQuestion.prompt?.match(/(?:letter|number)\s+([a-zA-Z0-9])/i) 
                                 || this.currentQuestion.prompt?.match(/trace\s+([a-zA-Z0-9])/i);
                character = promptMatch ? promptMatch[1].toUpperCase() : 'A';
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

        // ---- SUBMIT QUIZ (via dynamically created form on document.body) ----
        submitQuiz() {
            // Build a form OUTSIDE of any Alpine template and append to <body>
            const form = document.createElement('form');
            form.method = 'POST';
            // Use relative URL to avoid APP_URL port mismatch
            form.action = config.submitUrl;

            const addHidden = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };

            // CSRF token (from global config — bulletproof, no x-ref dependency)
            addHidden('_token', config.csrfToken);

            // Quiz data
            addHidden('score', this.score);
            addHidden('total', this.questions.length);
            addHidden('stars', this.starsEarned);
            addHidden('time_spent', this.timeSpent);
            addHidden('answers', JSON.stringify(this.answerLog));

            document.body.appendChild(form);
            form.submit();
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
</script>
@endpush
