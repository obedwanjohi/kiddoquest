@extends('kids.layouts.app')

@section('title', "{$world->name} — KiddoQuest")

@push('kid-styles')
<style>
    .world-bg {
        background: linear-gradient(180deg,
            {{ $world->theme_color }}33 0%,
            {{ $world->theme_color }}11 30%,
            #ffffff 100%);
    }
</style>
@endpush

@section('kid-content')
<x-kid.exit-bar :stars="$child->total_stars" :exitRoute="'kids.map'" :title="$world->name" />

<div class="pt-20 pb-12 min-h-screen world-bg">

    {{-- World Header --}}
    <div class="text-center px-4 mb-8 kid-fade-up">
        <div class="text-7xl mb-3 kid-float" style="filter: drop-shadow(0 4px 8px rgba(0,0,0,0.15));">
            {{ $world->icon }}
        </div>
        <h1 class="font-black kid-bounce-in"
            style="font-family: var(--kid-font-heading); font-size: var(--kid-text-hero); color: {{ $world->theme_color }}; text-shadow: 0 2px 0 rgba(255,255,255,0.6);">
            {{ $world->name }}
        </h1>
        @if($world->description)
            <p class="mt-2 max-w-md mx-auto" style="font-size: var(--kid-text-body); color: var(--kid-text-muted);">
                {{ $world->description }}
            </p>
        @endif
    </div>

    @if($world->slug === 'speak-repeat-safari')
        <div class="flex justify-center gap-2 mb-6 px-4 flex-wrap">
            <a href="{{ request()->fullUrlWithQuery(['tier' => null]) }}" class="px-4 py-2 rounded-full font-black text-sm transition-transform active:scale-95 shadow-sm {{ !request('tier') ? 'bg-blue-600 text-white ring-2 ring-blue-400' : 'bg-white text-gray-600' }}">
                🌟 All Tiers
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tier' => 'easy']) }}" class="px-4 py-2 rounded-full font-black text-sm transition-transform active:scale-95 shadow-sm {{ request('tier') === 'easy' ? 'bg-green-500 text-white ring-2 ring-green-300' : 'bg-white text-gray-600' }}">
                🟢 Easy
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tier' => 'medium']) }}" class="px-4 py-2 rounded-full font-black text-sm transition-transform active:scale-95 shadow-sm {{ request('tier') === 'medium' ? 'bg-amber-500 text-white ring-2 ring-amber-300' : 'bg-white text-gray-600' }}">
                🟡 Medium
            </a>
            <a href="{{ request()->fullUrlWithQuery(['tier' => 'hard']) }}" class="px-4 py-2 rounded-full font-black text-sm transition-transform active:scale-95 shadow-sm {{ request('tier') === 'hard' ? 'bg-red-500 text-white ring-2 ring-red-300' : 'bg-white text-gray-600' }}">
                🔴 Hard
            </a>
        </div>
    @endif

    {{-- Mission Trail --}}
    @if($missions->isEmpty())
        <div class="max-w-md mx-auto px-4">
            <div class="bg-white rounded-[var(--kid-radius-xl)] p-8 text-center shadow-[var(--kid-shadow-popup)] kid-pop">
                <div class="text-6xl mb-4 kid-float">🗺️</div>
                <h2 class="font-black mb-2" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title); color: var(--kid-text);">
                    Adventures Coming Soon!
                </h2>
                <p style="color: var(--kid-text-muted);">Leo is preparing fun missions for this world!</p>
            </div>
        </div>
    @else
        <div class="max-w-lg mx-auto px-4">
            @php
                $prevCompleted = true; // First mission always accessible
            @endphp

            @foreach($missions as $mission)
                @php
                    $progress = $child->missionProgress($mission); // We need to add missionProgress() to Child model
                    $isCompleted = $progress && $progress->status === 'completed';
                    $isInProgress = $progress && $progress->status === 'in_progress';
                    $isLocked = !$prevCompleted && !$isCompleted;
                    $storyTitle = $mission->display_title;
                    $hasQuiz = $mission->question_bank_id !== null;
                    $prevCompleted = $isCompleted; // For next iteration
                @endphp

                {{-- Trail connector (dotted path) --}}
                @if(!$loop->first)
                    <div class="flex justify-center -my-2 relative z-0">
                        <div class="w-1 h-10 rounded-full border-dashed"
                             style="border-left: 4px dotted {{ $isCompleted ? '#22C55E' : '#D1D5DB' }};"></div>
                    </div>
                @endif

                {{-- Mission Card --}}
                <div class="relative z-10 kid-bounce-in" style="animation-delay: {{ $loop->index * 120 }}ms;">
                    @if($isLocked)
                        {{-- Locked Mission --}}
                        <div class="bg-white/60 backdrop-blur rounded-[var(--kid-radius-xl)] p-5 flex items-center gap-4 shadow-[var(--kid-shadow-soft)] opacity-70">
                            <div class="flex-shrink-0 w-28 h-16 rounded-[var(--kid-radius-md)] flex items-center justify-center bg-gray-200 text-3xl overflow-hidden relative">
                                @if($mission->thumbnailMedia?->url)
                                    <img src="{{ $mission->thumbnailMedia->url }}" class="w-full h-full object-cover grayscale opacity-50" alt="">
                                    <div class="absolute inset-0 flex items-center justify-center z-10">🔒</div>
                                @else
                                    🔒
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-400">
                                    Mission {{ $loop->iteration }}
                                </div>
                                <h3 class="font-black text-gray-400" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
                                    {{ $storyTitle }}
                                </h3>
                            </div>
                        </div>
                    @else
                        {{-- Active Mission --}}
                        <a href="{{ route('kids.mission-video', [$world, $mission]) }}"
                           class="block bg-white rounded-[var(--kid-radius-xl)] p-5 shadow-[var(--kid-shadow-medium)]
                                  transition-all duration-300 hover:scale-[1.02] hover:shadow-[var(--kid-shadow-popup)]
                                  {{ $isCompleted ? 'ring-3 ring-[var(--kid-success)]' : '' }}"
                           style="border-left: 6px solid {{ $world->theme_color }};">
                            <div class="flex items-center gap-4">
                                {{-- Mission Number/Status Circle --}}
                                <div class="flex-shrink-0 w-28 h-16 rounded-[var(--kid-radius-md)] flex items-center justify-center text-2xl font-black text-white overflow-hidden relative shadow-sm"
                                     style="background: {{ $isCompleted ? '#22C55E' : ($isInProgress ? '#F59E0B' : $world->theme_color) }};">
                                    @if($mission->thumbnailMedia?->url)
                                        <img src="{{ $mission->thumbnailMedia->url }}" class="w-full h-full object-cover absolute inset-0 z-0" alt="">
                                        <div class="absolute inset-0 bg-black/20 z-10"></div>
                                    @endif
                                    
                                    <div class="relative z-20">
                                        @if($isCompleted)
                                            ✓
                                        @elseif($isInProgress)
                                            ▶
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Mission Info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold uppercase tracking-wide" style="color: {{ $world->theme_color }};">
                                        Mission {{ $loop->iteration }}
                                        @if($hasQuiz)
                                            <span class="ml-1">🎯</span>
                                        @endif
                                    </div>
                                    <h3 class="font-black truncate"
                                        style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission); color: var(--kid-text);">
                                        {{ $storyTitle }}
                                    </h3>
                                    @if($mission->description)
                                        <p class="text-sm truncate" style="color: var(--kid-text-muted);">{{ $mission->description }}</p>
                                    @endif
                                </div>

                                {{-- Action Arrow --}}
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white"
                                         style="background: {{ $world->theme_color }};">
                                        →
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endif
                </div>
            @endforeach

            {{-- World Complete Badge --}}
            @php
                $allComplete = true;
                foreach ($missions as $m) {
                    $p = $child->missionProgress($m);
                    if (!$p || $p->status !== 'completed') { $allComplete = false; break; }
                }
            @endphp
            @if($allComplete && $missions->count() > 0)
                <div class="flex justify-center mt-6 kid-bounce">
                    <div class="bg-gradient-to-r from-[var(--kid-warning)] to-[var(--kid-error)] text-white rounded-[var(--kid-radius-xl)] px-8 py-4 text-center shadow-[var(--kid-shadow-popup)]">
                        <div class="text-4xl mb-1">🏆</div>
                        <div class="font-black" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                            World Complete!
                        </div>
                        <div class="text-sm opacity-90">You earned all stars in {{ $world->name }}!</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Back to Map --}}
    <div class="text-center mt-8">
        <a href="{{ route('kids.map') }}">
            <x-kid.secondary-button icon="🗺️">Back to Map</x-kid.secondary-button>
        </a>
    </div>
</div>
@endsection