@extends('kids.layouts.app', ['kidTheme' => 'forest'])

@section('title', "Adventure Map — KiddoQuest CBC")

@push('kid-styles')
<style>
    .map-canvas {
        background: radial-gradient(circle at 50% 10%, #E0F2FE 0%, #D1FAE5 35%, #FEF3C7 75%, #FEE2E2 100%);
        min-height: 100vh;
    }
    
    /* 3D Tactile Nodes */
    .mission-node-btn {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        cursor: pointer;
        transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.15s ease;
        user-select: none;
    }
    .mission-node-btn:hover {
        transform: translateY(-4px) scale(1.08);
    }
    .mission-node-btn:active {
        transform: translateY(2px) scale(0.96);
    }

    .node-completed {
        background: linear-gradient(180deg, #34D399 0%, #059669 100%);
        border: 4px solid #FFFFFF;
        box-shadow: 0 8px 0 #047857, 0 12px 20px rgba(5, 150, 105, 0.3);
        color: #FFFFFF;
    }
    .node-completed:active {
        box-shadow: 0 2px 0 #047857;
    }

    .node-active {
        background: linear-gradient(180deg, #FBBF24 0%, #F59E0B 100%);
        border: 5px solid #FFFFFF;
        box-shadow: 0 8px 0 #D97706, 0 0 25px rgba(245, 158, 11, 0.6);
        color: #FFFFFF;
        animation: pulse-ring 1.8s ease-in-out infinite alternate;
    }
    .node-active:active {
        box-shadow: 0 2px 0 #D97706;
    }

    .node-locked {
        background: linear-gradient(180deg, #E2E8F0 0%, #CBD5E1 100%);
        border: 4px solid #FFFFFF;
        box-shadow: 0 6px 0 #94A3B8;
        color: #64748B;
        cursor: not-allowed;
    }

    @keyframes pulse-ring {
        0% { transform: scale(1); box-shadow: 0 8px 0 #D97706, 0 0 15px rgba(245, 158, 11, 0.4); }
        100% { transform: scale(1.08); box-shadow: 0 8px 0 #D97706, 0 0 35px rgba(245, 158, 11, 0.9); }
    }

    /* Winding trail dashes */
    .trail-line {
        width: 6px;
        height: 48px;
        background-image: repeating-linear-gradient(to bottom, #10B981, #10B981 8px, transparent 8px, transparent 16px);
        opacity: 0.7;
    }
</style>
@endpush

@section('kid-content')
{{-- Exit Bar: Stars + Coins + Exit --}}
<x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" :title="'Adventure Map'" />

<div x-data="{ activeSubject: 'all' }" class="pt-18 pb-28 min-h-screen map-canvas relative overflow-hidden">

    {{-- Floating Decorative Clouds & Scenery --}}
    <div class="absolute top-24 left-4 text-5xl opacity-40 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-44 right-6 text-6xl opacity-40 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-[480px] left-6 text-4xl opacity-30 pointer-events-none">🌴</div>
    <div class="absolute top-[800px] right-6 text-4xl opacity-30 pointer-events-none">🦒</div>
    <div class="absolute top-[1200px] left-8 text-4xl opacity-30 pointer-events-none">🐠</div>

    <div class="max-w-xl mx-auto px-4 relative z-10">

        {{-- 1. HERO MASCOT WELCOME CARD --}}
        <div class="mt-2 mb-5 bg-white/95 backdrop-blur-md rounded-3xl p-4 sm:p-5 shadow-xl border-4 border-white flex items-center justify-between gap-3 transform hover:scale-[1.01] transition">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="relative w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-yellow-300 border-2 border-amber-200 flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0 shadow-inner">
                    {{ $child->avatar_emoji ?? '🦁' }}
                    @if($child->equipped_hat_emoji)
                        <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 text-xl drop-shadow">
                            {{ $child->equipped_hat_emoji }}
                        </span>
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 px-2.5 py-0.5 rounded-full">
                            {{ $child->recommended_level ?? 'PP1' }}
                        </span>
                        <span class="text-xs">✨</span>
                    </div>
                    <h1 class="font-heading text-lg sm:text-xl font-black text-slate-900 truncate mt-0.5">
                        Welcome, {{ $child->name }}!
                    </h1>
                    <p class="text-xs text-slate-600 font-bold truncate">
                        Playing as {{ $child->avatar_name ?? 'Hero Buddy' }}
                    </p>
                </div>
            </div>

            {{-- Daily Streak Pill --}}
            <div class="flex flex-col items-end flex-shrink-0">
                <div class="flex items-center gap-1 bg-amber-50 border-2 border-amber-200 px-3 py-1 rounded-2xl shadow-sm">
                    <span class="text-base animate-bounce">🔥</span>
                    <span class="font-black text-xs text-amber-900">{{ $child->streak_days ?? 1 }} Day{{ ($child->streak_days ?? 1) > 1 ? 's' : '' }}!</span>
                </div>
                <a href="{{ route('kids.shop') }}" class="mt-1.5 text-[11px] font-black text-purple-700 hover:text-purple-900 flex items-center gap-1">
                    <span>🛍️ Shop</span> ➔
                </a>
            </div>
        </div>

        {{-- 2. SUBJECT WORLD SELECTOR TABS (4 Big Playful Buttons) --}}
        <div class="mb-8">
            <div class="text-center mb-2">
                <span class="text-[11px] font-black text-slate-700 uppercase tracking-widest bg-white/80 backdrop-blur-md px-3.5 py-1 rounded-full border border-slate-200">
                    Choose Subject World 🎒
                </span>
            </div>
            
            <div class="grid grid-cols-4 gap-2">
                {{-- All Worlds --}}
                <button @click="activeSubject = 'all'"
                        :class="activeSubject === 'all' ? 'bg-indigo-600 text-white shadow-[0_5px_0_#3730A3] translate-y-[-2px]' : 'bg-white text-slate-700 border-2 border-slate-200 shadow-sm'"
                        class="py-3 px-1 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-1 cursor-pointer">
                    <span class="text-2xl">🗺️</span>
                    <span class="text-[11px] font-black leading-tight">All Worlds</span>
                </button>

                {{-- Math Safari --}}
                <button @click="activeSubject = 'math'"
                        :class="activeSubject === 'math' ? 'bg-amber-500 text-slate-950 shadow-[0_5px_0_#B45309] translate-y-[-2px]' : 'bg-white text-slate-700 border-2 border-slate-200 shadow-sm'"
                        class="py-3 px-1 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-1 cursor-pointer">
                    <span class="text-2xl">🔢</span>
                    <span class="text-[11px] font-black leading-tight">Math</span>
                </button>

                {{-- Language & Phonics --}}
                <button @click="activeSubject = 'english'"
                        :class="activeSubject === 'english' ? 'bg-sky-500 text-white shadow-[0_5px_0_#0369A1] translate-y-[-2px]' : 'bg-white text-slate-700 border-2 border-slate-200 shadow-sm'"
                        class="py-3 px-1 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-1 cursor-pointer">
                    <span class="text-2xl">📖</span>
                    <span class="text-[11px] font-black leading-tight">Phonics</span>
                </button>

                {{-- CRE & Values --}}
                <button @click="activeSubject = 'cre'"
                        :class="activeSubject === 'cre' ? 'bg-emerald-600 text-white shadow-[0_5px_0_#065F46] translate-y-[-2px]' : 'bg-white text-slate-700 border-2 border-slate-200 shadow-sm'"
                        class="py-3 px-1 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-1 cursor-pointer">
                    <span class="text-2xl">✝️</span>
                    <span class="text-[11px] font-black leading-tight">Values</span>
                </button>
            </div>
        </div>

        {{-- 3. ADVENTURE WORLDS & WINDING STEPPING-STONE TRAILS --}}
        @php
            $prevCompleted = true;
            $foundCurrent = false;
        @endphp

        @foreach($worlds as $wIdx => $world)
            @php
                $missions = $world->missions ?? collect();
                $missionsCount = $missions->count();
                $completedCount = 0;
                foreach ($missions as $m) {
                    $p = $child->missionProgress($m);
                    if ($p && $p->status === 'completed') $completedCount++;
                }

                $subjectCat = $world->subject_category ?? 'all';
                $worldThemes = [
                    'whispering-forest' => ['bg' => 'from-emerald-500 to-teal-700', 'accent' => 'bg-emerald-100 text-emerald-900', 'icon' => '🌲'],
                    'safari-plains'     => ['bg' => 'from-amber-500 to-orange-600', 'accent' => 'bg-amber-100 text-amber-950', 'icon' => '🦁'],
                    'ocean-cove'        => ['bg' => 'from-blue-500 to-indigo-700', 'accent' => 'bg-blue-100 text-blue-900', 'icon' => '🌊'],
                    'castle-of-discovery' => ['bg' => 'from-purple-500 to-pink-600', 'accent' => 'bg-purple-100 text-purple-950', 'icon' => '🏰'],
                ];
                $theme = $worldThemes[$world->slug] ?? ['bg' => 'from-purple-600 to-indigo-700', 'accent' => 'bg-purple-100 text-purple-900', 'icon' => '⭐'];
            @endphp

            <div x-show="activeSubject === 'all' || activeSubject === '{{ $subjectCat }}'" class="mb-14">
                
                {{-- WORLD BIOME CARD --}}
                <div class="bg-gradient-to-r {{ $theme['bg'] }} text-white rounded-3xl p-5 shadow-2xl border-4 border-white relative overflow-hidden mb-6">
                    <div class="absolute -right-6 -bottom-6 text-7xl opacity-25 pointer-events-none">
                        {{ $world->icon ?? $theme['icon'] }}
                    </div>
                    
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-3.5">
                            <span class="text-4xl sm:text-5xl filter drop-shadow">{{ $world->icon ?? $theme['icon'] }}</span>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider {{ $theme['accent'] }} px-2.5 py-0.5 rounded-full inline-block mb-1">
                                    {{ $world->subject_name ?? 'CBC World' }}
                                </span>
                                <h2 class="font-heading text-xl sm:text-2xl font-black leading-tight">
                                    {{ $world->name }}
                                </h2>
                                <p class="text-xs text-white/80 font-bold mt-0.5">
                                    {{ $completedCount }} / {{ max(1, $missionsCount) }} Missions Mastered ⭐
                                </p>
                            </div>
                        </div>

                        {{-- World Progress Badge --}}
                        <div class="text-right">
                            <span class="text-2xl">🏆</span>
                        </div>
                    </div>
                </div>

                {{-- WINDING STEPPING STONES PATH --}}
                <div class="flex flex-col items-center gap-3 relative py-2">
                    
                    @if($missions->isEmpty())
                        {{-- Ready for 1st Mission Launchpad --}}
                        <div class="bg-white/90 backdrop-blur-md rounded-3xl p-6 text-center border-2 border-white shadow-lg w-full max-w-sm">
                            <div class="text-4xl mb-2 animate-bounce">🚀</div>
                            <h3 class="font-heading text-base font-black text-slate-800 mb-1">
                                Ready for Mission #1!
                            </h3>
                            <p class="text-xs text-slate-600 font-bold mb-4">
                                Our very first CBC learning mission will launch right here.
                            </p>
                            <a href="{{ route('kids.profiles') }}" 
                               class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black text-xs px-6 py-2.5 rounded-xl shadow-md hover:scale-105 transition cursor-pointer">
                                🌟 Explore Other Worlds
                            </a>
                        </div>
                    @else
                        @foreach($missions as $mIdx => $mission)
                            @php
                                $progress = $child->missionProgress($mission);
                                $isCompleted = $progress && $progress->status === 'completed';
                                $starsEarned = $progress->stars_earned ?? 0;

                                $isUnlocked = $prevCompleted || $isCompleted;
                                $isActiveTarget = $isUnlocked && !$isCompleted && !$foundCurrent;

                                if ($isActiveTarget) {
                                    $foundCurrent = true;
                                }
                                $prevCompleted = $isCompleted;

                                // Serpentine positions: Center -> Left -> Center -> Right
                                $alignment = ['justify-center', 'justify-start pl-8 sm:pl-16', 'justify-center', 'justify-end pr-8 sm:pr-16'];
                                $alignClass = $alignment[($mIdx) % 4];
                            @endphp

                            {{-- Connecting Trail Line --}}
                            @if(!$loop->first)
                                <div class="trail-line"></div>
                            @endif

                            {{-- MISSION NODE --}}
                            <div class="w-full flex {{ $alignClass }}">
                                
                                @if($isCompleted)
                                    {{-- COMPLETED NODE (Green 3D + Stars) --}}
                                    <a href="{{ route('kids.mission.intro', $mission) }}" class="flex flex-col items-center group">
                                        <div class="mission-node-btn node-completed">
                                            <span class="text-2xl font-black">✓</span>
                                            <span class="text-[10px] font-black mt-0.5">Lv {{ $mIdx + 1 }}</span>
                                        </div>
                                        {{-- 3-Star Rating --}}
                                        <div class="flex items-center gap-0.5 mt-1.5 text-xs">
                                            <span class="{{ $starsEarned >= 1 ? 'text-amber-400' : 'text-slate-300' }}">⭐</span>
                                            <span class="{{ $starsEarned >= 2 ? 'text-amber-400' : 'text-slate-300' }}">⭐</span>
                                            <span class="{{ $starsEarned >= 3 ? 'text-amber-400' : 'text-slate-300' }}">⭐</span>
                                        </div>
                                        <span class="text-xs font-black text-slate-800 mt-0.5 text-center max-w-[130px] truncate">
                                            {{ $mission->title }}
                                        </span>
                                    </a>

                                @elseif($isActiveTarget)
                                    {{-- ACTIVE PLAY NODE (Glowing Orange Pulse) --}}
                                    <a href="{{ route('kids.mission.intro', $mission) }}" class="flex flex-col items-center group">
                                        <div class="relative">
                                            {{-- Tap To Play Speech Bubble --}}
                                            <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-amber-500 text-slate-950 font-black text-[10px] px-3 py-0.5 rounded-full whitespace-nowrap shadow-md animate-bounce border border-white">
                                                PLAY! 🚀
                                            </div>
                                            <div class="mission-node-btn node-active">
                                                <span class="text-2xl font-black">⭐</span>
                                                <span class="text-[10px] font-black mt-0.5">Lv {{ $mIdx + 1 }}</span>
                                            </div>
                                        </div>
                                        <span class="text-xs font-black text-amber-900 bg-white/90 px-3 py-1 rounded-full shadow-sm mt-2 text-center max-w-[140px] truncate border border-amber-200">
                                            {{ $mission->title }}
                                        </span>
                                    </a>

                                @else
                                    {{-- LOCKED NODE (Silver Lock) --}}
                                    <div class="flex flex-col items-center opacity-60">
                                        <div class="mission-node-btn node-locked">
                                            <span class="text-xl">🔒</span>
                                            <span class="text-[10px] font-black mt-0.5">Lv {{ $mIdx + 1 }}</span>
                                        </div>
                                        <span class="text-xs font-bold text-slate-500 mt-1.5 text-center max-w-[120px] truncate">
                                            {{ $mission->title }}
                                        </span>
                                    </div>
                                @endif

                            </div>
                        @endforeach
                    @endif

                </div>

            </div>
        @endforeach

    </div>

</div>
@endsection