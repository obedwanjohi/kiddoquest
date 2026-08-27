@extends('kids.layouts.app', ['kidTheme' => 'forest'])

@section('title', "Adventure Map — KiddoQuest CBC")

@push('kid-styles')
<style>
    .map-path-bg {
        background: radial-gradient(circle at 50% 0%, #D1FAE5 0%, #A7F3D0 40%, #6EE7B7 100%);
    }
    .node-pulse {
        animation: node-glow 1.8s ease-in-out infinite alternate;
    }
    @keyframes node-glow {
        0% { transform: scale(1); box-shadow: 0 0 15px rgba(245,158,11,0.5); }
        100% { transform: scale(1.08); box-shadow: 0 0 30px rgba(245,158,11,0.9); }
    }
    .avatar-marker {
        animation: avatar-bounce 1.5s ease-in-out infinite;
    }
    @keyframes avatar-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
</style>
@endpush

@section('kid-content')
{{-- Top Bar: Exit + Stars + Coins --}}
<x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" :title="'Adventure Map'" />

<div x-data="{ activeSubject: 'all' }" class="pt-20 pb-28 min-h-screen map-path-bg relative overflow-hidden">

    {{-- Floating Background Clouds & Decor --}}
    <div class="absolute top-24 left-4 text-6xl opacity-30 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-48 right-6 text-7xl opacity-30 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-[600px] left-8 text-5xl opacity-20 pointer-events-none">🌳</div>
    <div class="absolute top-[1000px] right-10 text-5xl opacity-20 pointer-events-none">🌲</div>

    <div class="max-w-2xl mx-auto px-4 relative z-10">

        {{-- Parent Assigned Focus Mission Banner --}}
        @if($child->assigned_mission_id && ($assignedM = \App\Models\Mission::find($child->assigned_mission_id)))
            <div class="mb-4 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-900 rounded-2xl p-3 border-2 border-amber-300 shadow-lg flex items-center justify-between animate-bounce">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">📌</span>
                    <div>
                        <div class="font-black text-xs uppercase tracking-wider text-amber-950">Parent Focus Challenge Today!</div>
                        <div class="font-black text-sm text-slate-900">{{ $assignedM->title }}</div>
                    </div>
                </div>
                <span class="bg-slate-900 text-amber-300 text-xs font-black px-3 py-1 rounded-full shadow-sm">
                    Focus 🌟
                </span>
            </div>
        @endif

        {{-- Leo Mascot Banner --}}
        <div class="mb-6 kid-fade-up">
            <div class="flex items-end gap-3">
                <div class="text-6xl kid-float">🦁</div>
                <x-kid.mascot-bubble variant="cloud">
                    @if($child->has_played)
                        <strong>Welcome back, {{ $child->name }}!</strong><br>
                        Pick your subject world below to start learning! 🌟
                    @else
                        <strong>Hi {{ $child->name }}! I'm Leo! 🦁</strong><br>
                        Choose what you want to learn today!
                    @endif
                </x-kid.mascot-bubble>
            </div>
        </div>

        {{-- 3 Subject World Pools Selector --}}
        <div class="bg-white/90 backdrop-blur-md rounded-3xl p-3 shadow-lg mb-6 border-2 border-white text-center">
            <div class="text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Choose Subject World Pool 🎒</div>
            <div class="grid grid-cols-4 gap-1.5">
                <button @click="activeSubject = 'all'"
                        :class="activeSubject === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="py-2.5 px-2 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                    <span class="text-lg">🗺️</span>
                    <span class="truncate">All Worlds</span>
                </button>
                <button @click="activeSubject = 'math'"
                        :class="activeSubject === 'math' ? 'bg-amber-500 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="py-2.5 px-2 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                    <span class="text-lg">🔢</span>
                    <span class="truncate">Math</span>
                </button>
                <button @click="activeSubject = 'english'"
                        :class="activeSubject === 'english' ? 'bg-blue-600 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="py-2.5 px-2 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                    <span class="text-lg">📖</span>
                    <span class="truncate">Phonics</span>
                </button>
                <button @click="activeSubject = 'cre'"
                        :class="activeSubject === 'cre' ? 'bg-emerald-600 text-white shadow-md' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="py-2.5 px-2 rounded-2xl font-black text-xs transition-all flex flex-col items-center justify-center gap-0.5 cursor-pointer">
                    <span class="text-lg">✝️</span>
                    <span class="truncate">CRE Values</span>
                </button>
            </div>
        </div>

        {{-- Streak & Shop Quick Card --}}
        <div class="bg-white/90 backdrop-blur-md rounded-3xl p-3 sm:p-4 shadow-[0_6px_0_rgba(16,185,129,0.2)] mb-8 flex flex-wrap items-center justify-between gap-2 border-2 border-emerald-100 kid-pop">
            
            {{-- Streak & Back to Dashboard --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('kids.profiles') }}" class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-2xl font-black text-xs transition-all border border-slate-200 shadow-sm">
                    <span>🏠</span>
                    <span>Dashboard</span>
                </a>
                <div class="flex items-center gap-1.5">
                    <span class="text-2xl animate-bounce">🔥</span>
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Daily Streak</div>
                        <div class="font-black text-emerald-700 text-xs sm:text-sm" style="font-family: var(--kid-font-heading);">
                            {{ $child->streak_days ?? 1 }} Day{{ ($child->streak_days ?? 1) > 1 ? 's' : '' }}!
                        </div>
                    </div>
                </div>
            </div>

            {{-- Premium Shop & Toy Bazaar Button --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('kids.shop') }}" class="flex items-center gap-2 bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 px-3.5 py-2 rounded-2xl font-black shadow-[0_4px_0_#b45309] active:shadow-none active:translate-y-1 transition-all text-xs border-2 border-yellow-200 cursor-pointer">
                    <span class="text-lg animate-bounce">🛍️</span>
                    <span class="font-black text-sm">🪙 {{ number_format($child->star_coins ?? 0) }}</span>
                    <span class="bg-slate-950/15 text-slate-950 px-2 py-0.5 rounded-lg text-[10px] uppercase font-black tracking-wider">Toy Shop ✨</span>
                </a>
            </div>
        </div>

        {{-- Winding Map Path --}}
        @php
            $prevMissionCompleted = true; // First mission is accessible
            $foundCurrentActive = false;
        @endphp

        @foreach($worlds as $worldIndex => $world)
            @php
                $missions = $world->missions;
                $worldMissionsCount = $missions->count();
                $completedInWorld = 0;
                foreach ($missions as $m) {
                    $p = $child->missionProgress($m);
                    if ($p && $p->status === 'completed') { $completedInWorld++; }
                }
                $worldComplete = $worldMissionsCount > 0 && $completedInWorld === $worldMissionsCount;
                $worldStarted = $completedInWorld > 0;
                $isWorldLocked = $world->is_locked && !$worldStarted && $worldIndex > 0;
                $subjectCat = $world->subject_category;
            @endphp

            {{-- World Region Block (Filtered by activeSubject) --}}
            <div x-show="activeSubject === 'all' || activeSubject === '{{ $subjectCat }}'" class="mb-12">

                {{-- World Region Banner --}}
                <div class="my-6 text-center relative">
                    <div class="inline-flex flex-col items-center bg-white/95 backdrop-blur-md px-6 py-3 rounded-3xl shadow-[0_6px_0_rgba(0,0,0,0.08)] border-2 border-white">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ $world->icon }}</span>
                            <div class="text-left">
                                <h2 class="font-black text-lg leading-tight" style="font-family: var(--kid-font-heading); color: {{ $world->theme_color }};">
                                    {{ $world->name }}
                                </h2>
                                <span class="text-xs font-bold text-gray-500">
                                    {{ $completedInWorld }}/{{ $worldMissionsCount }} Missions Complete
                                </span>
                            </div>
                        </div>
                        {{-- Subject Pool Badge Tag --}}
                        <div class="mt-1.5 px-3 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                            Subject Pool: {{ $world->subject_name }}
                        </div>
                    </div>
                </div>

            {{-- Winding Path Nodes for this World --}}
            <div class="flex flex-col items-center gap-4 relative">

                @foreach($missions as $missionIndex => $mission)
                    @php
                        $progress = $child->missionProgress($mission);
                        $isCompleted = $progress && $progress->status === 'completed';
                        $isInProgress = $progress && $progress->status === 'in_progress';
                        $starsEarned = $progress->stars_earned ?? 0;

                        // Lock logic: active if previous was completed or it's the very first mission
                        $isAccessible = $prevMissionCompleted || $isCompleted || $isInProgress;
                        $isCurrentTarget = $isAccessible && !$isCompleted && !$foundCurrentActive;
                        
                        if ($isCurrentTarget) {
                            $foundCurrentActive = true;
                        }

                        $prevMissionCompleted = $isCompleted;

                        // Serpentine Zigzag positioning (Center -> Left -> Center -> Right -> Center...)
                        $positions = ['justify-center', 'justify-start pl-8 sm:pl-16', 'justify-center', 'justify-end pr-8 sm:pr-16'];
                        $posClass = $positions[($missionIndex + $worldIndex) % 4];
                    @endphp

                    {{-- Curved Connecting Path Line --}}
                    @if(!$loop->first)
                        <div class="w-full flex justify-center -my-2">
                            <div class="w-2 h-10 border-l-4 border-dashed rounded-full"
                                 style="border-color: {{ $isCompleted ? '#10B981' : '#9CA3AF' }}; opacity: 0.6;"></div>
                        </div>
                    @endif

                    {{-- Mission Node Wrapper --}}
                    <div class="w-full flex {{ $posClass }} relative z-10">

                        @if(!$isAccessible || $isWorldLocked)
                            {{-- Locked Node --}}
                            <div class="flex flex-col items-center gap-1 opacity-60 cursor-not-allowed">
                                <div class="w-20 h-20 rounded-full bg-gray-200 border-4 border-gray-300 flex items-center justify-center shadow-inner text-3xl">
                                    🔒
                                </div>
                                <span class="text-xs font-black text-gray-500 bg-white/70 px-2 py-0.5 rounded-full">
                                    Mission {{ $missionIndex + 1 }}
                                </span>
                            </div>
                        @else
                            {{-- Accessible Node --}}
                            <a href="{{ route('kids.mission-video', [$world, $mission]) }}" 
                               class="flex flex-col items-center gap-1 group relative touch-manipulation">

                                {{-- Avatar Position Marker (On Current Active Node) --}}
                                @if($isCurrentTarget)
                                    <div class="absolute -top-12 z-30 avatar-marker flex flex-col items-center">
                                        <div class="relative">
                                            <span class="text-4xl md:text-5xl drop-shadow-md">{{ $child->avatar_emoji }}</span>
                                            @if($child->equipped_hat_emoji)
                                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 text-2xl md:text-3xl">{{ $child->equipped_hat_emoji }}</span>
                                            @endif
                                        </div>
                                        <div class="bg-amber-400 text-amber-950 font-black text-[10px] uppercase px-2 py-0.5 rounded-full shadow-md animate-pulse">
                                            YOU ARE HERE!
                                        </div>
                                    </div>
                                @endif

                                {{-- Star Rating Badge (Above Node if Completed) --}}
                                @if($isCompleted)
                                    <div class="flex gap-0.5 bg-white/90 px-2 py-0.5 rounded-full shadow-sm text-sm border border-amber-200 mb-0.5">
                                        @for($s = 1; $s <= 3; $s++)
                                            <span style="opacity: {{ $s <= $starsEarned ? '1' : '0.25' }}">⭐</span>
                                        @endfor
                                    </div>
                                @endif

                                {{-- The Stone Button --}}
                                <div class="w-20 h-20 md:w-24 md:h-24 rounded-full flex items-center justify-center text-white text-3xl font-black transition-transform group-active:scale-90
                                            {{ $isCurrentTarget ? 'node-pulse bg-gradient-to-b from-amber-400 to-amber-500 border-4 border-white shadow-lg' : ($isCompleted ? 'bg-gradient-to-b from-emerald-400 to-emerald-500 border-4 border-emerald-200 shadow-[0_6px_0_#047857]' : 'bg-gradient-to-b from-blue-400 to-blue-500 border-4 border-blue-200 shadow-[0_6px_0_#1d4ed8]') }}">
                                    @if($isCompleted)
                                        <span class="text-4xl drop-shadow-md">✓</span>
                                    @elseif($isCurrentTarget)
                                        <span class="text-4xl animate-bounce">▶</span>
                                    @else
                                        <span>{{ $missionIndex + 1 }}</span>
                                    @endif
                                </div>

                                {{-- Mission Label --}}
                                <div class="bg-white/90 backdrop-blur-sm px-3 py-1 rounded-2xl shadow-sm text-center border border-white max-w-[140px]">
                                    <div class="text-xs font-black text-gray-800 truncate" style="font-family: var(--kid-font-heading);">
                                        {{ $mission->display_title }}
                                    </div>
                                </div>
                            </a>
                        @endif

                    </div>
                @endforeach

            </div>
        </div>
        @endforeach

        {{-- Final Victory Marker --}}
        <div class="text-center mt-12 mb-6">
            <div class="inline-block text-6xl animate-bounce">🏆</div>
            <h3 class="font-black text-xl text-emerald-900 mt-2" style="font-family: var(--kid-font-heading);">
                Keep learning to unlock the entire map!
            </h3>
        </div>

    </div>
</div>
@endsection