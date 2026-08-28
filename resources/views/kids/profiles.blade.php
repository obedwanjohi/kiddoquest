@extends('kids.layouts.app')

@section('title', "Who's Playing Today? — KiddoQuest CBC")

@section('kid-content')
<div class="h-[100dvh] max-h-[100dvh] bg-gradient-to-b from-[#DDD6FE] via-[#FEF3C7] to-[#FCE7F3] flex flex-col justify-between p-3 sm:p-5 relative overflow-hidden select-none">

    {{-- Decorative Background Floaters --}}
    <div class="absolute top-8 left-4 text-3xl opacity-30 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-10 right-6 text-3xl opacity-30 pointer-events-none animate-pulse">⭐</div>
    <div class="absolute bottom-10 left-6 text-2xl opacity-20 pointer-events-none">🎈</div>
    <div class="absolute bottom-12 right-6 text-2xl opacity-20 pointer-events-none">✨</div>

    {{-- 1. TOP HEADER WITH INTEGRATED PARENT & HOME BUTTONS (No footer clutter) --}}
    <header class="w-full max-w-xl mx-auto relative z-10 flex items-center justify-between gap-2 pt-1">
        {{-- Left: Home / Website --}}
        <a href="{{ url('/') }}" 
           class="flex items-center gap-1 bg-white/90 hover:bg-white text-slate-800 border border-slate-200 px-3 py-1.5 rounded-xl font-black text-xs shadow-xs active:scale-95 transition cursor-pointer flex-shrink-0"
           title="Go to Website">
            <span class="text-sm">🏠</span>
            <span class="hidden sm:inline">Home</span>
        </a>

        {{-- Center: Title Badge --}}
        <div class="text-center min-w-0">
            <div class="inline-flex items-center gap-1.5 bg-white/90 backdrop-blur-md px-3 py-1 rounded-full border border-purple-100 shadow-xs">
                <span class="text-base">🦁</span>
                <span class="font-heading font-black text-xs sm:text-sm text-slate-900 truncate">
                    Who's Playing?
                </span>
            </div>
        </div>

        {{-- Right: Parent Zone PIN Lock --}}
        <a href="{{ route('parent.pin_gate') }}" 
           class="flex items-center gap-1 bg-white/90 hover:bg-white text-purple-900 border border-purple-200 px-3 py-1.5 rounded-xl font-black text-xs shadow-xs active:scale-95 transition cursor-pointer flex-shrink-0"
           title="Parent Zone (PIN Protected)">
            <span class="text-sm">🔐</span>
            <span class="hidden sm:inline">Parents</span>
        </a>
    </header>

    {{-- 2. CENTER STAGE: CHARACTER PROFILE CARDS --}}
    <main class="w-full max-w-lg mx-auto flex-1 flex flex-col items-center justify-center relative z-10 my-auto px-1">
        
        @if($children->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-5 text-center shadow-xl border-2 border-white max-w-sm w-full">
                <div class="text-5xl mb-2 animate-bounce">🧒</div>
                <h2 class="font-heading text-lg font-black text-slate-900 mb-1">
                    No Adventurers Yet!
                </h2>
                <p class="text-xs text-slate-600 font-bold mb-4">
                    Add your child to unlock personalized games, stories, and star rewards!
                </p>
                <a href="{{ route('guardian.children.create') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white font-heading font-black text-xs py-3 px-5 rounded-2xl shadow-md hover:scale-102 transition cursor-pointer">
                    <span>✨ Add Your First Child</span>
                    <span>→</span>
                </a>
            </div>
        @else
            {{-- 2-Column Responsive Card Grid (Fits 100% on any mobile screen) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 w-full">
                @foreach($children as $child)
                    <a href="{{ route('kids.enter', $child) }}"
                       class="group bg-white/95 backdrop-blur-md rounded-2xl sm:rounded-3xl p-3 sm:p-4 text-center shadow-lg border-2 border-white hover:border-purple-300 hover:shadow-xl transition transform hover:-translate-y-1 active:scale-95 flex flex-col items-center justify-between min-h-[145px] sm:min-h-[165px] relative overflow-hidden cursor-pointer">
                        
                        {{-- Top Badge: Level / Status --}}
                        <div class="w-full flex items-center justify-between mb-1">
                            <span class="text-[8px] sm:text-[9px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full">
                                {{ $child->recommended_level ?? 'PP1' }}
                            </span>
                            @if(!$child->has_played)
                                <span class="text-[8px] font-black text-rose-600 bg-rose-50 px-1 py-0.5 rounded-full border border-rose-200 animate-pulse">
                                    NEW ✨
                                </span>
                            @endif
                        </div>

                        {{-- Avatar Buddy with Equipped Hat --}}
                        <div class="relative my-0.5">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-gradient-to-tr from-amber-100 to-purple-100 border border-amber-200 flex items-center justify-center text-2xl sm:text-3xl shadow-inner group-hover:scale-105 transition transform">
                                {{ $child->avatar_emoji ?? '🦁' }}
                            </div>
                            @if($child->equipped_hat_emoji)
                                <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 text-sm drop-shadow animate-bounce">
                                    {{ $child->equipped_hat_emoji }}
                                </span>
                            @endif
                        </div>

                        {{-- Child Name --}}
                        <div class="w-full">
                            <h2 class="font-heading text-xs sm:text-sm font-black text-slate-900 truncate leading-tight group-hover:text-purple-700 transition">
                                {{ $child->name }}
                            </h2>
                            <p class="text-[9px] sm:text-[10px] text-slate-500 font-bold truncate">
                                {{ $child->avatar_name ?? 'Hero Friend' }}
                            </p>
                        </div>

                        {{-- Stars & Play Pill --}}
                        <div class="w-full mt-1 pt-1 border-t border-slate-100 flex items-center justify-between">
                            <span class="inline-flex items-center gap-0.5 text-[10px] font-black text-amber-900 bg-amber-50 px-1.5 py-0.5 rounded">
                                <span>⭐</span> {{ number_format($child->total_stars ?? 0) }}
                            </span>
                            <span class="text-[9px] font-black text-purple-700 group-hover:translate-x-0.5 transition flex items-center">
                                Play ➔
                            </span>
                        </div>

                    </a>
                @endforeach

                {{-- Add New Child Card --}}
                <a href="{{ route('guardian.children.create') }}"
                   class="group bg-white/60 hover:bg-white/90 backdrop-blur-md rounded-2xl sm:rounded-3xl p-3 sm:p-4 text-center border-2 border-dashed border-purple-300 hover:border-purple-500 transition transform hover:-translate-y-1 active:scale-95 flex flex-col items-center justify-center min-h-[145px] sm:min-h-[165px] shadow-xs hover:shadow-md cursor-pointer">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-purple-100 group-hover:bg-purple-600 text-purple-700 group-hover:text-white flex items-center justify-center text-xl sm:text-2xl shadow-xs transition mb-1">
                        ➕
                    </div>
                    <h3 class="font-heading text-[11px] sm:text-xs font-black text-slate-800 group-hover:text-purple-700 transition">
                        Add Explorer
                    </h3>
                    <p class="text-[9px] text-slate-500 font-bold">
                        New Child Profile
                    </p>
                </a>
            </div>
        @endif

    </main>

    {{-- 3. SUBTLE BOTTOM HINT --}}
    <footer class="w-full max-w-md mx-auto text-center pb-1 relative z-10">
        <p class="text-[10px] sm:text-[11px] font-bold text-slate-600/80">
            ✨ Tap a picture to jump into your CBC adventure!
        </p>
    </footer>

</div>
@endsection