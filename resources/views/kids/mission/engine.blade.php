@extends('kids.layouts.app')

@section('title', "{$mission->title} — Mission Time!")

@push('kid-styles')
<link rel="stylesheet" href="{{ asset('css/kids/mission/core.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/drag_sequence.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/drag_sort.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/matching.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/true_false.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/pattern.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/count_objects.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/tracing.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/kids/mission/types/speak_repeat.css') }}?v={{ time() }}">
@endpush

@section('kid-content')

@php
    // Build questions JSON for Alpine.js — REAL data from database
    $questionsJson = $mission->questions->map(function ($q) {
        // Normalize slug: DB uses hyphens (multiple-choice), JS uses underscores (multiple_choice)
        $rawSlug = $q->quizType ? $q->quizType->slug : 'multiple-choice';
        $typeSlug = str_replace('-', '_', $rawSlug);
        
        // Fix naming mismatches between DB and frontend templates
        if ($typeSlug === 'complete_pattern') {
            $typeSlug = 'pattern';
        }
        
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
            'narration_text' => $q->narration_text,
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
            'scoring_config' => $q->scoring_config,
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

@php
    $correctSoundUrl = \App\Models\Media::where('type', 'ILIKE', '%audio%')->where('name', 'ILIKE', '%correct%')->first()?->url;
    $wrongSoundUrl   = \App\Models\Media::where('type', 'ILIKE', '%audio%')->where('name', 'ILIKE', '%wrong%')->first()?->url;
    $celebSoundUrl   = \App\Models\Media::where('type', 'ILIKE', '%audio%')->where('name', 'ILIKE', '%celebration%')->first()?->url;
@endphp
<script>
    window.KID_SOUND_FX = {
        correct: @json($correctSoundUrl),
        wrong: @json($wrongSoundUrl),
        celebration: @json($celebSoundUrl),
    };

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
<div class="screen-container" x-data="quizEngine(window.__quizConfig)" x-init="init()" @click="if(window.KidSoundLayer) window.KidSoundLayer.init()">

    <!-- Loading -->
    <div x-show="!initialized" style="display:flex;align-items:center;justify-content:center;height:100%;flex-direction:column;gap:16px;">
        <div style="font-size:64px;animation:kid-wiggle 2s ease-in-out infinite;">🦁</div>
        <p style="font-size:24px;font-weight:900;">Loading Quiz...</p>
    </div>

    <!-- HEADER -->
    <div class="header" x-show="initialized" x-cloak>
        <a href="#" class="icon-btn" @click.prevent="exitQuiz()">🏠</a>
        <div class="progress-bar">
            <div class="progress-bar-fill" :style="`width: ${progressPercent}%`"></div>
        </div>
        <a href="#" class="icon-btn">⚙️</a>
    </div>

    <template x-if="!quizComplete && currentQuestion">
        <div class="content-wrapper" x-show="initialized" x-cloak>
            
            <div class="lion-column">
                <div class="lion-emoji" :class="{ celebrating: leoCelebrating }">🦁</div>
                <div class="speech-bubble">
                    <button @click="playQuestionAudio()" class="audio-btn" aria-label="Replay Audio">🔊</button>
                    <!-- Show Leo message if exists, else question prompt -->
                    <span x-html="currentQuestion.prompt.replace(/\n/g, '<br>')"></span>
                </div>
            </div>

            <div class="action-column">
                <!-- Render global image EXCEPT for specific layouts that use their own images -->
                <template x-if="currentQuestion.image && !['pattern', 'speak_repeat', 'speak-repeat', 'count_objects', 'count-objects', 'multiple_choice', 'tap_answer', 'listen_choose'].includes(currentQuestion.type)">
                    <img :src="currentQuestion.image" alt="Question" class="main-image" x-on:error="currentQuestion.image = null">
                </template>

                @include('kids.mission.types.multiple_choice')
                @include('kids.mission.types.fill_blank')
                @include('kids.mission.types.drag_sequence')
                @include('kids.mission.types.drag_sort')
                @include('kids.mission.types.matching')
                @include('kids.mission.types.true_false')
                @include('kids.mission.types.pattern')
                @include('kids.mission.types.count_objects')
                @include('kids.mission.types.tracing')
                @include('kids.mission.types.speak_repeat')
                <!-- OTHER QUESTION TYPES WILL GO HERE LATER -->
                <template x-if="!['multiple_choice','tap_answer','listen_choose','fill_blank','drag_sequence','drag_sort','matching','true_false','pattern','count_objects','count-objects','tracing','speak_repeat','speak-repeat'].includes(currentQuestion.type)">
                    <div style="text-align:center; padding: 20px;">
                        <h3>Type: <span x-text="currentQuestion.type"></span></h3>
                        <p>Layout not ported yet. Please test multiple_choice.</p>
                    </div>
                </template>

            </div>
        </div>
    </template>

    {{-- COMPLETION SCREEN --}}
    <template x-if="quizComplete">
        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; gap: 20px;">
            <div style="font-size:80px;">🎉</div>
            <h2 style="font-size:32px; font-weight:900;">Mission Complete!</h2>
            <div style="font-size:24px;">You earned <span x-text="starsEarned" style="color:var(--btn-yellow-dark);font-weight:bold;"></span> stars!</div>
            <button @click="submitQuiz()" style="margin-top:20px; padding:16px 32px; font-size:20px; font-weight:800; background:var(--btn-green); color:white; border:none; border-radius:20px; box-shadow:0 6px 0 var(--btn-green-dark); cursor:pointer;">Continue</button>
        </div>
    </template>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/kid/quiz-event-bus.js') }}"></script>
<script src="{{ asset('js/kid/quiz-sound-layer.js') }}"></script>
<script src="{{ asset('js/kid/quiz-reward-layer.js') }}"></script>
<script src="{{ asset('js/kid/quiz-engine.js') }}?v={{ time() }}"></script>
@endpush
