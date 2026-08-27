@extends('kids.layouts.app')

@section('title', "Who's Playing? — KiddoQuest CBC")

@section('kid-content')
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-8"
     style="background: linear-gradient(180deg, #C4B5FD 0%, #FDE68A 50%, #FBCFE8 100%);">

    {{-- Title --}}
    <h1 class="font-black text-center mb-2 kid-bounce-in"
        style="font-family: var(--kid-font-heading); font-size: var(--kid-text-hero); color: var(--kid-text); text-shadow: 0 2px 0 rgba(255,255,255,0.5);">
        🌟 Who's Playing? 🌟
    </h1>
    <p class="text-center mb-8 kid-fade-up"
       style="font-size: var(--kid-text-body); color: var(--kid-text-muted);">
        Tap your picture to start learning!
    </p>

    @if($children->isEmpty())
        {{-- Empty State --}}
        <div class="bg-white rounded-[var(--kid-radius-xl)] p-8 text-center shadow-[var(--kid-shadow-popup)] max-w-md w-full kid-pop">
            <div class="text-7xl mb-4 kid-float">🧒</div>
            <h2 class="font-black mb-2" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title); color: var(--kid-text);">
                No Adventurers Yet!
            </h2>
            <p class="mb-6" style="font-size: var(--kid-text-body); color: var(--kid-text-muted);">
                Let's add your first child to begin the learning adventure.
            </p>
            <a href="{{ route('guardian.children.create') }}">
                <x-kid.button icon="➕" label="Add Your First Child" />
            </a>
        </div>
    @else
        {{-- Child Profile Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6 max-w-3xl w-full mb-8">
            @foreach($children as $child)
                <a href="{{ route('kids.enter', $child) }}"
                   class="block bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-xl)] shadow-[var(--kid-shadow-medium)] p-6 text-center
                          transition-all duration-300 hover:scale-105 hover:shadow-[var(--kid-shadow-popup)]
                          kid-bounce-in"
                   style="animation-delay: {{ $loop->index * 100 }}ms;">

                    {{-- Avatar --}}
                    <div class="text-6xl mb-3 {{ $child->has_played ? '' : 'kid-float' }}">{{ $child->avatar_emoji }}</div>

                    {{-- Name --}}
                    <h3 class="font-black mb-1"
                        style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title); color: var(--kid-text);">
                        {{ $child->name }}
                    </h3>

                    {{-- Stars --}}
                    <div class="inline-flex items-center gap-1.5 bg-[var(--kid-bg)] rounded-full px-4 py-2 mt-2">
                        <span class="text-lg">⭐</span>
                        <span class="font-black tabular-nums"
                              style="font-size: var(--kid-text-counter); color: var(--kid-encourage-dark);">{{ $child->total_stars }}</span>
                    </div>

                    @if($child->has_played)
                        <p class="mt-2" style="font-size: var(--kid-text-caption); color: var(--kid-text-light);">
                            {{ $child->last_played_display }}
                        </p>
                    @else
                        <p class="mt-2 font-bold" style="font-size: var(--kid-text-caption); color: var(--kid-primary);">
                            ✨ New Player
                        </p>
                    @endif
                </a>
            @endforeach

            {{-- Add New Child Card --}}
            <a href="{{ route('guardian.children.create') }}"
               class="block border-[3px] border-dashed border-white/70 rounded-[var(--kid-radius-xl)] p-6 text-center
                      flex flex-col items-center justify-center
                      transition-all duration-300 hover:bg-white/20 hover:scale-105
                      min-h-[200px]">
                <div class="text-5xl mb-3 opacity-70">➕</div>
                <div class="font-black text-white"
                     style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
                    Add Child
                </div>
            </a>
        </div>
    @endif

    {{-- Bottom Action Row --}}
    <div class="mt-4 flex flex-wrap items-center justify-center gap-4">
        <a href="{{ route('parent.pin_gate') }}">
            <x-kid.secondary-button icon="🔐">Parent Zone (PIN Protected)</x-kid.secondary-button>
        </a>
        <a href="{{ url('/') }}" class="bg-white/80 hover:bg-white text-slate-700 font-bold px-5 py-2.5 rounded-full shadow-sm text-sm transition">
            🏠 Home
        </a>
    </div>
</div>
@endsection