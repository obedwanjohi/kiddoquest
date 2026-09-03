@extends('kids.layouts.app', ['kidTheme' => 'safari'])

@section('title', "Songs & Music Hub — KiddoQuest CBC")

@section('kid-content')
<x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" :title="'🎵 Music & Songs Hub'" />

<div class="pt-20 sm:pt-24 pb-28 min-h-screen bg-gradient-to-b from-purple-900 via-indigo-900 to-slate-950 text-white px-4 relative overflow-hidden">
    
    {{-- Background Stars & Lights --}}
    <div class="absolute top-10 left-5 text-4xl opacity-20 animate-pulse">⭐</div>
    <div class="absolute top-40 right-5 text-4xl opacity-20 animate-pulse">🎵</div>
    
    <div class="max-w-lg mx-auto relative z-10">

        {{-- Banner Card --}}
        <div class="bg-gradient-to-r from-pink-500 via-purple-600 to-indigo-600 rounded-3xl p-5 shadow-2xl border-2 border-white/20 mb-6 text-center relative overflow-hidden">
            <div class="text-5xl mb-2 animate-bounce">🎶</div>
            <h1 class="font-heading font-black text-xl sm:text-2xl mb-1 drop-shadow">
                Leo's Music & Songs Hub!
            </h1>
            <p class="text-xs text-white/90 font-bold max-w-xs mx-auto">
                Relax, sing along, and enjoy fun educational songs and praise melodies!
            </p>
        </div>

        {{-- Song Cards Grid --}}
        <div class="grid grid-cols-1 gap-4">
            @foreach($songs as $song)
                <div x-data="{ playing: false }" class="bg-white/10 backdrop-blur-md rounded-3xl p-4 border border-white/15 shadow-xl transition-all hover:bg-white/15">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr {{ $song['bg'] }} flex items-center justify-center text-2xl shadow-md flex-shrink-0">
                                {{ $song['emoji'] }}
                            </div>
                            <div>
                                <span class="text-[9px] font-black uppercase tracking-wider bg-white/20 px-2 py-0.5 rounded-full inline-block mb-0.5">
                                    {{ $song['category'] }}
                                </span>
                                <h3 class="font-heading font-black text-base leading-snug">
                                    {{ $song['title'] }}
                                </h3>
                            </div>
                        </div>

                        <button @click="playing = !playing"
                                class="px-4 py-2 rounded-2xl font-black text-xs transition-transform active:scale-95 shadow-md flex items-center gap-1 {{ $song['bg'] }}">
                            <span x-text="playing ? '⏹️ Close' : '▶️ Play'">▶️ Play</span>
                        </button>
                    </div>

                    {{-- Embed Player --}}
                    <template x-if="playing">
                        <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-inner mt-2 border border-white/20">
                            <iframe class="w-full h-full" :src="`{{ $song['video_url'] }}?autoplay=1`" title="Song Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    </template>
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
