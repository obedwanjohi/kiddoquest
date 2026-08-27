@extends('kids.layouts.app')

@section('title', 'Kid UI — Component Showcase')

@section('kid-content')
<div class="min-h-screen" style="background: var(--kid-bg);">

    {{-- Demo Exit Bar --}}
    <div class="mb-8 relative" style="height: 80px;">
        <div class="kid-exit-bar absolute top-0 left-0 right-0
                    flex items-center justify-between px-4 py-3
                    bg-white/80 backdrop-blur-md shadow-[var(--kid-shadow-soft)]">
            <div class="flex items-center justify-center w-12 h-12 rounded-full bg-[var(--kid-bg)] shadow-[var(--kid-shadow-soft)]">
                <span class="text-2xl">🗺️</span>
            </div>
            <h1 class="font-black text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
                Showcase
            </h1>
            <x-kid.star-counter :count="42" />
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 pb-20 space-y-12">

        {{-- 1. Buttons --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🔘 Buttons
            </h2>
            <div class="bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)] p-6 shadow-[var(--kid-shadow-soft)] space-y-4">
                <div class="flex flex-wrap gap-4">
                    <x-kid.button>Tap Me!</x-kid.button>
                    <x-kid.button color="success">✓ Correct</x-kid.button>
                    <x-kid.button color="warning">Try Again</x-kid.button>
                    <x-kid.button color="danger">✗ Wrong</x-kid.button>
                </div>
                <div class="flex flex-wrap gap-4">
                    <x-kid.secondary-button>Skip</x-kid.secondary-button>
                    <x-kid.secondary-button>← Back</x-kid.secondary-button>
                </div>
            </div>
        </section>

        {{-- 2. Answer Cards --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🃏 Answer Cards
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-kid.answer-card emoji="🍎" label="Apple" />
                <x-kid.answer-card emoji="🍌" label="Banana" selected="true" />
                <x-kid.answer-card emoji="🍇" label="Grapes" />
                <x-kid.answer-card emoji="🍊" label="Orange" state="correct" />
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <x-kid.answer-card text="Blue" state="wrong" />
                <x-kid.answer-card text="Red" />
                <x-kid.answer-card text="Green" state="correct" />
                <x-kid.answer-card text="Yellow" />
            </div>
        </section>

        {{-- 3. Star Counter --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                ⭐ Star Counter
            </h2>
            <div class="bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)] p-6 shadow-[var(--kid-shadow-soft)] flex gap-6 items-center">
                <x-kid.star-counter :count="0" />
                <x-kid.star-counter :count="5" />
                <x-kid.star-counter :count="42" />
                <x-kid.star-counter :count="999" />
            </div>
        </section>

        {{-- 4. Progress Bar --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                📊 Progress Bar
            </h2>
            <div class="bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)] p-6 shadow-[var(--kid-shadow-soft)] space-y-4">
                <x-kid.progress-bar :value="25" label="World Progress" />
                <x-kid.progress-bar :value="68" label="Quiz Score" />
                <x-kid.progress-bar :value="100" label="Complete!" />
            </div>
        </section>

        {{-- 5. Mascot Bubble --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🦁 Mascot Bubble
            </h2>
            <div class="bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)] p-6 shadow-[var(--kid-shadow-soft)]">
                <x-kid.mascot-bubble text="Hi there! I'm Leo. Ready to learn?" :name="true" />
            </div>
        </section>

        {{-- 6. World Cards --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🌍 World Cards
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-kid.world-card :world="['id' => 1, 'name' => 'Alphabet Forest', 'description' => 'Learn your ABCs!', 'icon' => '🔤', 'theme' => 'forest']" :progress="75" />
                <x-kid.world-card :world="['id' => 2, 'name' => 'Number Safari', 'description' => 'Count with animals', 'icon' => '🔢', 'theme' => 'savanna']" :progress="30" />
                <x-kid.world-card :world="['id' => 3, 'name' => 'Color Ocean', 'description' => 'Unlock at 50 stars', 'icon' => '🎨', 'theme' => 'ocean']" :locked="true" />
            </div>
        </section>

        {{-- 7. Mission Cards --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🎯 Mission Cards
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-kid.mission-card :mission="['id' => 1, 'title' => 'Meet Letter A', 'icon' => '🔤']" :stars="3" />
                <x-kid.mission-card :mission="['id' => 2, 'title' => 'Meet Letter B', 'icon' => '🐝']" :stars="1" />
                <x-kid.mission-card :mission="['id' => 3, 'title' => 'Locked Mission', 'icon' => '🔒']" :locked="true" />
            </div>
        </section>

        {{-- 8. Avatar Cards --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                👤 Avatar Cards
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-kid.avatar-card :child="['id' => 1, 'name' => 'Emma', 'avatar' => 'lion', 'age' => 5, 'level' => 'PP1', 'stars' => 42, 'progress' => 68]" />
                <x-kid.avatar-card :child="['id' => 2, 'name' => 'Noah', 'avatar' => 'panda', 'age' => 4, 'level' => 'Playgroup', 'stars' => 15, 'progress' => 25]" />
                <x-kid.avatar-card :child="['id' => 3, 'name' => 'Zoe', 'avatar' => 'rabbit', 'age' => 6, 'level' => 'PP2', 'stars' => 99, 'progress' => 90]" />
            </div>
        </section>

        {{-- 9. Animations Demo --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                ✨ Animations
            </h2>
            <div class="bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)] p-6 shadow-[var(--kid-shadow-soft)]">
                <div class="flex flex-wrap gap-8 justify-center text-center">
                    <div>
                        <div class="text-5xl kid-bounce-in mb-2">🎉</div>
                        <p class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">Bounce In</p>
                    </div>
                    <div>
                        <div class="text-5xl kid-pop mb-2">⭐</div>
                        <p class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">Pop</p>
                    </div>
                    <div>
                        <div class="text-5xl kid-float mb-2">🎈</div>
                        <p class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">Float</p>
                    </div>
                    <div>
                        <div class="text-5xl kid-wiggle mb-2">🐛</div>
                        <p class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">Wiggle</p>
                    </div>
                    <div>
                        <div class="text-5xl kid-pulse mb-2">❤️</div>
                        <p class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">Pulse</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- 10. World Themes --}}
        <section>
            <h2 class="font-black mb-4 text-[var(--kid-text)]" style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
                🎨 World Themes
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="world-forest rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🌲</span>
                    <p class="text-white font-bold mt-2">Forest</p>
                </div>
                <div class="world-savanna rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🦁</span>
                    <p class="text-white font-bold mt-2">Savanna</p>
                </div>
                <div class="world-ocean rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🐠</span>
                    <p class="text-white font-bold mt-2">Ocean</p>
                </div>
                <div class="world-space rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🚀</span>
                    <p class="text-white font-bold mt-2">Space</p>
                </div>
                <div class="world-candy rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🍭</span>
                    <p class="text-white font-bold mt-2">Candy</p>
                </div>
                <div class="world-arctic rounded-[var(--kid-radius-md)] p-6 text-center" style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
                    <span class="text-4xl">🐧</span>
                    <p class="text-white font-bold mt-2">Arctic</p>
                </div>
            </div>
        </section>

    </div>
</div>
@endsection