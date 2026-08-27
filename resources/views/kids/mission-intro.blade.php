@extends('kids.layouts.app')

@section('title', "Mission Briefing — BZabc Kids")

@push('kid-styles')
<style>
    .intro-bg {
        background: linear-gradient(180deg,
            {{ $world->theme_color }}55 0%,
            {{ $world->theme_color }}22 50%,
            #ffffff 100%);
    }
</style>
@endpush

@section('kid-content')
<x-kid.exit-bar :stars="$child->total_stars" :exitRoute="'kids.world'" :exitRouteParam="$world" :title="'Mission Briefing'" />

<div class="pt-20 pb-12 min-h-screen intro-bg flex items-center justify-center px-4 intro-container">

    <div class="max-w-lg w-full">

        {{-- Mission Badge --}}
        <div class="text-center mb-6 kid-bounce-in intro-badge">
            <div class="inline-block bg-white/80 backdrop-blur rounded-full px-5 py-2 shadow-[var(--kid-shadow-soft)]">
                <span class="font-black" style="font-size: var(--kid-text-body); color: {{ $world->theme_color }};">
                    🎯 Mission: {{ $storyTitle }}
                </span>
            </div>
            @if(!empty($isRetest))
                <div class="mt-2 inline-block bg-emerald-500 text-white rounded-full px-4 py-1 text-xs font-black shadow-md animate-bounce">
                    ✨ Fresh Practice Questions Ready!
                </div>
            @endif
        </div>

        {{-- Leo Story Scene --}}
        <div class="bg-white rounded-[var(--kid-radius-xl)] p-8 shadow-[var(--kid-shadow-popup)] kid-pop intro-card">

            {{-- Leo + World Mascot --}}
            <div class="flex items-end justify-center gap-2 mb-6 intro-mascot-row">
                <div class="text-7xl kid-float">🦁</div>
                <div class="text-5xl {{ $world->icon ? '' : '' }} kid-wiggle"
                     style="animation-delay: 0.3s; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                    {{ $world->icon }}
                </div>
            </div>

            {{-- Story Bubble --}}
            <div class="mb-6">
                <x-kid.mascot-bubble variant="cloud" align="center">
                    @php
                        // Build personalized greeting
                        $childName = $child->name ?? 'adventurer';
                        if (!empty($isRetest)) {
                            $greeting = "Hey {$childName}! Leo prepared fresh new practice questions for your challenge today! 🎯";
                        } else {
                            $greeting = "Hey {$childName}! Are you ready for a fun mission?";
                        }

                        $storyText = $mission->intro_narration_text ?: $greeting;
                    @endphp
                    {{ $storyText }}
                </x-kid.mascot-bubble>
            </div>

            {{-- Mission Info Card --}}
            <div class="bg-[var(--kid-bg)] rounded-[var(--kid-radius-md)] p-4 mb-6 text-center intro-info-card">
                <div class="flex items-center justify-around">
                    {{-- Duration --}}
                    <div class="text-center">
                        <div class="text-2xl mb-1">⏱️</div>
                        <div class="font-black text-lg" style="color: var(--kid-text);">{{ $mission->estimated_minutes ?? 5 }}</div>
                        <div class="text-xs" style="color: var(--kid-text-light);">minutes</div>
                    </div>

                    {{-- Divider --}}
                    <div class="w-px h-12 bg-gray-200"></div>

                    {{-- Stars to earn --}}
                    <div class="text-center">
                        <div class="text-2xl mb-1">⭐</div>
                        <div class="font-black text-lg" style="color: var(--kid-encourage-dark);">{{ $mission->stars_reward ?? 3 }}</div>
                        <div class="text-xs" style="color: var(--kid-text-light);">stars</div>
                    </div>

                    @if($mission->question_bank_id)
                    {{-- Divider --}}
                    <div class="w-px h-12 bg-gray-200"></div>

                    {{-- Questions --}}
                    <div class="text-center">
                        <div class="text-2xl mb-1">❓</div>
                        <div class="font-black text-lg" style="color: var(--kid-primary);">
                            {{ $mission->questions_per_session ?? 10 }}
                        </div>
                        <div class="text-xs" style="color: var(--kid-text-light);">questions</div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Start Button --}}
            <div class="text-center">
                @if($mission->video_url || $mission->video_media_id || $mission->question_bank_id)
                    <a href="{{ route('kids.mission-video', [$world, $mission]) }}">
                        <x-kid.button icon="🚀" label="Start Mission!" size="lg" />
                    </a>
                @else
                    <div class="bg-yellow-50 border-2 border-yellow-200 rounded-[var(--kid-radius-md)] p-4">
                        <span style="color: var(--kid-text-muted);">🎯 Mission coming soon!</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Back button --}}
        <div class="text-center mt-6">
            <a href="{{ route('kids.world', $world) }}">
                <x-kid.secondary-button icon="←">Back to {{ $world->name }}</x-kid.secondary-button>
            </a>
        </div>
    </div>
</div>
@endsection