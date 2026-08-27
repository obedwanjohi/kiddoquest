@php
    // Resolve child from session (celebration route is a closure)
    $childId = session('active_child_id');
    $child = $childId ? \App\Models\Child::find($childId) : null;

    // Get celebration data from flash session
    $celebration = session('celebration', []);
    $stars = $celebration['stars'] ?? 3;
    $score = $celebration['score'] ?? null;
    $total = $celebration['total'] ?? null;

    // Leo's lines — varied by performance
    if ($stars === 3) {
        $leoLine = "WOW! Perfect score! You're a superstar!";
        $headline = "PERFECT!";
        $headlineEmoji = "🏆";
    } elseif ($stars === 2) {
        $leoLine = "Great job! You're getting so smart!";
        $headline = "Awesome!";
        $headlineEmoji = "🎉";
    } elseif ($stars === 1) {
        $leoLine = "Good try! Practice makes perfect!";
        $headline = "Nice Try!";
        $headlineEmoji = "💪";
    } else {
        $leoLine = "Don't worry! Trying is how we learn!";
        $headline = "Keep Going!";
        $headlineEmoji = "🌟";
    }

    if ($child) {
        $leoLine = $child->name . ', ' . lcfirst($leoLine);
    }
@endphp

@extends('kids.layouts.app')
@section('title', $headline . ' — BZabc Kids')

@push('kid-styles')
<style>
    .celebration-bg {
        background: radial-gradient(ellipse at top, #fde68a 0%, #fbbf24 40%, #f59e0b 100%);
    }
    .confetti {
        position: fixed; width: 10px; height: 10px; top: -20px;
        z-index: 5; pointer-events: none;
        animation: confetti-fall linear infinite;
    }
    @keyframes confetti-fall {
        0% { transform: translateY(0) rotate(0deg); opacity: 1; }
        100% { transform: translateY(110vh) rotate(720deg); opacity: 0.8; }
    }
    .star-earned {
        font-size: 3rem;
        display: inline-block;
        animation: star-pop 0.5s ease-out backwards;
    }
    @media (min-width: 640px) {
        .star-earned { font-size: 4.5rem; }
    }
    .star-earned:nth-child(1) { animation-delay: 0.2s; }
    .star-earned:nth-child(2) { animation-delay: 0.4s; }
    .star-earned:nth-child(3) { animation-delay: 0.6s; }
    @keyframes star-pop {
        0% { transform: scale(0) rotate(-180deg); opacity: 0; }
        70% { transform: scale(1.2) rotate(10deg); }
        100% { transform: scale(1) rotate(0); opacity: 1; }
    }
</style>
@endpush

@section('kid-content')
<div class="min-h-screen celebration-bg flex items-center justify-center p-3 sm:p-4 relative overflow-y-auto">

    {{-- Confetti Container --}}
    <div id="confetti-container"></div>

    {{-- Center Card --}}
    <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-5 sm:p-8 max-w-sm sm:max-w-md w-full text-center relative z-10 my-4 border-4 border-amber-200">

        {{-- Big Emoji --}}
        <div class="text-6xl sm:text-7xl mb-1 animate-bounce">{{ $headlineEmoji }}</div>

        {{-- Headline --}}
        <h1 class="text-3xl sm:text-4xl font-black text-amber-500 mb-1" style="font-family: var(--kid-font-heading);">
            {{ $headline }}
        </h1>

        {{-- Score --}}
        @if ($score !== null && $total !== null)
            <p class="text-sm sm:text-base font-bold text-gray-500 mb-3">
                You got {{ $score }} out of {{ $total }} right!
            </p>
        @endif

        {{-- Stars Earned --}}
        <div class="flex justify-center gap-2 sm:gap-3 mb-4">
            @for ($i = 0; $i < 3; $i++)
                @if ($i < $stars)
                    <span class="star-earned">⭐</span>
                @else
                    <span class="star-earned" style="opacity: 0.2; filter: grayscale(1);">⭐</span>
                @endif
            @endfor
        </div>

        {{-- Leo Speech Bubble --}}
        <div class="mb-4 flex items-center justify-center gap-2 bg-amber-50 rounded-2xl p-2.5 border border-amber-200">
            <span class="text-3xl flex-shrink-0 animate-pulse">🦁</span>
            <span class="text-xs sm:text-sm font-black text-amber-900 text-left">
                {{ $leoLine }}
            </span>
        </div>

        {{-- Total Stars & Coins Bar --}}
        @if ($child)
            @if(isset($celebration['earned_coins']) && $celebration['earned_coins'] > 0)
                <div class="bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-2xl p-2.5 mb-3 flex items-center justify-center gap-2 shadow-md animate-bounce border-2 border-yellow-200">
                    <span class="text-2xl">🪙</span>
                    <span class="font-black text-sm sm:text-base">
                        +{{ $celebration['earned_coins'] }} Star Coins Earned!
                    </span>
                </div>
            @endif

            <div class="bg-gray-100 rounded-2xl p-2.5 mb-5 flex items-center justify-around text-xs sm:text-sm font-extrabold">
                <div class="flex items-center gap-1.5 text-amber-600">
                    <span class="text-xl">⭐</span>
                    <span>{{ $child->total_stars }} Stars</span>
                </div>
                <div class="flex items-center gap-1.5 text-amber-700">
                    <span class="text-xl">🪙</span>
                    <span>{{ $child->star_coins ?? 0 }} Coins</span>
                </div>
            </div>
        @endif

        {{-- Buttons --}}
        <div class="flex flex-col gap-2.5">
            <a href="{{ route('kids.map') }}" class="block w-full">
                <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-3 rounded-2xl shadow-[0_4px_0_#059669] active:translate-y-0.5 active:shadow-none transition-all flex items-center justify-center gap-2 text-base">
                    <span>🗺️</span> Back to Map
                </button>
            </a>

            @if(\Illuminate\Support\Facades\Route::has('kids.shop'))
                <a href="{{ route('kids.shop') }}" class="block w-full">
                    <button class="w-full bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black py-3 rounded-2xl shadow-[0_4px_0_#b45309] border-2 border-yellow-100 active:translate-y-0.5 active:shadow-none transition-all flex items-center justify-center gap-2 text-base">
                        <span>🛍️</span> Visit Reward Shop
                    </button>
                </a>
            @endif
        </div>
    </div>
</div>

@push('kid-scripts')
<script>
    // 🎉 Generate confetti
    (function() {
        const container = document.getElementById('confetti-container');
        if (!container) return;
        const colors = ['#ef4444','#f59e0b','#22c55e','#3b82f6','#a855f7','#ec4899'];

        for (let i = 0; i < 25; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
            confetti.style.animationDelay = Math.random() * 1.5 + 's';
            container.appendChild(confetti);
        }
    })();
</script>
@endpush
@endsection