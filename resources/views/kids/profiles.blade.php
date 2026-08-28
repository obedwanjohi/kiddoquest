@extends('kids.layouts.app')

@section('title', "Who's Playing Today? — KiddoQuest CBC")

@section('kid-content')
<div class="min-h-screen bg-gradient-to-b from-[#DDD6FE] via-[#FEF3C7] to-[#FCE7F3] flex flex-col items-center justify-between px-3 sm:px-6 py-6 sm:py-10 relative overflow-x-hidden">

    {{-- Floating Decorative Elements --}}
    <div class="absolute top-6 left-4 text-4xl sm:text-5xl opacity-40 pointer-events-none animate-pulse">☁️</div>
    <div class="absolute top-12 right-6 text-4xl sm:text-5xl opacity-40 pointer-events-none animate-pulse">⭐</div>
    <div class="absolute bottom-16 left-6 text-3xl sm:text-4xl opacity-30 pointer-events-none">🎈</div>
    <div class="absolute bottom-20 right-6 text-3xl sm:text-4xl opacity-30 pointer-events-none">✨</div>

    {{-- HEADER SECTION --}}
    <header class="w-full max-w-xl text-center relative z-10 pt-2 sm:pt-4 mb-4 sm:mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-gradient-to-tr from-amber-400 to-yellow-300 text-3xl sm:text-4xl shadow-xl shadow-amber-500/20 border-2 border-white mb-2 transform hover:scale-110 transition">
            🦁
        </div>
        <h1 class="font-heading text-2xl sm:text-4xl font-black text-slate-900 tracking-tight leading-tight"
            style="text-shadow: 0 2px 0 rgba(255,255,255,0.8);">
            Who's Playing Today?
        </h1>
        <p class="text-xs sm:text-sm text-slate-700 font-bold mt-1 max-w-xs mx-auto">
            Tap your hero picture to jump into your CBC adventure! 🚀
        </p>
    </header>

    {{-- MAIN PROFILES CONTAINER --}}
    <main class="w-full max-w-2xl mx-auto flex-1 flex flex-col items-center justify-center relative z-10 my-auto">
        
        @if($children->isEmpty())
            {{-- Empty State --}}
            <div class="bg-white/95 backdrop-blur-md rounded-3xl p-6 sm:p-8 text-center shadow-2xl border-4 border-white max-w-md w-full my-4">
                <div class="text-6xl mb-3 animate-bounce">🧒</div>
                <h2 class="font-heading text-xl sm:text-2xl font-black text-slate-900 mb-1">
                    No Adventurers Yet!
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 font-bold mb-6">
                    Add your child to unlock personalized games, stories, and star rewards!
                </p>
                <a href="{{ route('guardian.children.create') }}" 
                   class="inline-flex items-center justify-center gap-2 w-full bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white font-heading font-black text-sm sm:text-base py-3.5 px-6 rounded-2xl shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:scale-102 active:scale-98 transition cursor-pointer">
                    <span>✨ Add Your First Child</span>
                    <span>→</span>
                </a>
            </div>
        @else
            {{-- Responsive Profile Cards Grid (2 cols on mobile, 3 cols on tablet/desktop) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-5 w-full">
                @foreach($children as $child)
                    <a href="{{ route('kids.enter', $child) }}"
                       class="group bg-white/95 backdrop-blur-md rounded-3xl p-4 sm:p-5 text-center shadow-xl border-3 sm:border-4 border-white hover:border-purple-300 hover:shadow-2xl transition transform hover:-translate-y-1.5 active:scale-95 flex flex-col items-center justify-between min-h-[190px] sm:min-h-[220px] relative overflow-hidden">
                        
                        {{-- Top Badge: Level / Status --}}
                        <div class="w-full flex items-center justify-between mb-1.5">
                            <span class="text-[9px] sm:text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">
                                {{ $child->recommended_level ?? 'PP1' }}
                            </span>
                            @if(!$child->has_played)
                                <span class="text-[9px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded-full border border-rose-200 animate-pulse">
                                    NEW ✨
                                </span>
                            @endif
                        </div>

                        {{-- Avatar Buddy with Equipped Hat --}}
                        <div class="relative my-1">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-amber-100 to-purple-100 border-2 border-amber-200 flex items-center justify-center text-3xl sm:text-4xl shadow-inner group-hover:scale-110 transition transform">
                                {{ $child->avatar_emoji ?? '🦁' }}
                            </div>
                            @if($child->equipped_hat_emoji)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 text-lg sm:text-xl drop-shadow animate-bounce">
                                    {{ $child->equipped_hat_emoji }}
                                </span>
                            @endif
                        </div>

                        {{-- Child Name --}}
                        <div class="w-full">
                            <h2 class="font-heading text-sm sm:text-base font-black text-slate-900 truncate leading-tight group-hover:text-purple-700 transition">
                                {{ $child->name }}
                            </h2>
                            <p class="text-[10px] sm:text-[11px] text-slate-500 font-bold truncate mt-0.5">
                                {{ $child->avatar_name ?? 'Hero Friend' }}
                            </p>
                        </div>

                        {{-- Stars & Play Pill --}}
                        <div class="w-full mt-2 pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1 text-[11px] sm:text-xs font-black text-amber-900 bg-amber-50 px-2 py-0.5 rounded-lg">
                                <span>⭐</span> {{ number_format($child->total_stars ?? 0) }}
                            </span>
                            <span class="text-[10px] font-black text-purple-700 group-hover:translate-x-0.5 transition flex items-center">
                                Play ➔
                            </span>
                        </div>

                    </a>
                @endforeach

                {{-- Add New Child Card --}}
                <a href="{{ route('guardian.children.create') }}"
                   class="group bg-white/50 hover:bg-white/90 backdrop-blur-md rounded-3xl p-4 sm:p-5 text-center border-3 border-dashed border-purple-300 hover:border-purple-500 transition transform hover:-translate-y-1.5 active:scale-95 flex flex-col items-center justify-center min-h-[190px] sm:min-h-[220px] shadow-sm hover:shadow-xl cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-purple-100 group-hover:bg-purple-600 text-purple-700 group-hover:text-white flex items-center justify-center text-2xl sm:text-3xl shadow-sm transition mb-2">
                        ➕
                    </div>
                    <h3 class="font-heading text-xs sm:text-sm font-black text-slate-800 group-hover:text-purple-700 transition">
                        Add Explorer
                    </h3>
                    <p class="text-[10px] text-slate-500 font-bold mt-0.5">
                        New Child Profile
                    </p>
                </a>
            </div>
        @endif

    </main>

    {{-- FOOTER CONTROLS ROW (Mobile-optimized) --}}
    <footer class="w-full max-w-lg mx-auto flex items-center justify-center gap-3 pt-6 relative z-10">
        <a href="{{ route('parent.pin_gate') }}" 
           class="flex items-center gap-1.5 bg-white/90 hover:bg-white text-slate-800 border-2 border-purple-200 px-4 py-2.5 rounded-2xl font-black text-xs shadow-sm hover:shadow-md transition active:scale-95 cursor-pointer">
            <span>🔐</span>
            <span>Parent Zone</span>
        </a>
        
        <a href="{{ url('/') }}" 
           class="flex items-center gap-1.5 bg-white/90 hover:bg-white text-slate-700 border-2 border-slate-200 px-4 py-2.5 rounded-2xl font-black text-xs shadow-sm hover:shadow-md transition active:scale-95 cursor-pointer">
            <span>🏠</span>
            <span>Website</span>
        </a>
    </footer>

</div>
@endsection