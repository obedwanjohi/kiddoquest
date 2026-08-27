{{--
    KID UI — Exit Bar (Top navigation bar)
    Usage: <x-kid.exit-bar :stars="$child->total_stars" :coins="$child->star_coins" />
    Always visible at top. Contains exit button + coins + star counter.
--}}
@props([
    'stars' => 0,
    'coins' => null,
    'exitRoute' => 'kids.map',
    'exitRouteParam' => null,
    'title' => null,
])

@php
    if ($coins === null && session('active_child_id')) {
        $c = \App\Models\Child::find(session('active_child_id'));
        $coins = $c->star_coins ?? 0;
    }
    $coins = $coins ?? 0;
@endphp

<div class="kid-exit-bar fixed top-0 left-0 right-0 z-40
            flex items-center justify-between px-3 py-2.5
            bg-white/90 backdrop-blur-md shadow-[var(--kid-shadow-soft)]">

    {{-- Exit / Back Button (Left) --}}
    @php
        $isMapPage = request()->routeIs('kids.map');
        $homeUrl = route('kids.profiles');
        $mapUrl = route('kids.map');
    @endphp
    <div class="flex items-center gap-2">
        @if($isMapPage)
            <a href="{{ $homeUrl }}"
               class="flex items-center gap-1 px-3 py-1.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-[0_3px_0_#3730a3] active:translate-y-0.5 active:shadow-none transition-all border border-indigo-400"
               aria-label="Back to Profiles Dashboard">
                <span class="text-base">🏠</span>
                <span>Home</span>
            </a>
        @else
            <a href="{{ $mapUrl }}"
               class="flex items-center gap-1 px-3 py-1.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-[0_3px_0_#065f46] active:translate-y-0.5 active:shadow-none transition-all border border-emerald-400"
               aria-label="Back to Map">
                <span class="text-base">🗺️</span>
                <span>Map</span>
            </a>
        @endif

        {{-- Premium Shop Button --}}
        @if(\Illuminate\Support\Facades\Route::has('kids.shop'))
            <a href="{{ route('kids.shop') }}"
               class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-2xl bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-500 hover:from-amber-500 hover:to-amber-600 text-slate-950 font-black text-xs shadow-[0_3px_0_#b45309] border-2 border-yellow-100 active:translate-y-0.5 active:shadow-none transition-all cursor-pointer"
               title="Star Bazaar Shop">
                <span class="text-base animate-bounce">🛍️</span>
                <span class="font-black text-sm text-slate-900">{{ number_format($coins) }}</span>
                <span class="bg-slate-950/15 text-slate-900 px-1.5 py-0.5 rounded-lg text-[10px] uppercase tracking-wider font-black">Shop</span>
            </a>
        @endif
    </div>

    {{-- Title (Center) --}}
    @if($title)
        <h1 class="font-black text-[var(--kid-text)] truncate max-w-[150px] sm:max-w-xs text-center"
            style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
            {{ $title }}
        </h1>
    @endif

    {{-- Star Counter & Parent Zone Link (Right) --}}
    <div class="flex items-center gap-2">
        <x-kid.star-counter :count="$stars" />
        @if(\Illuminate\Support\Facades\Route::has('parent.pin_gate'))
            <a href="{{ route('parent.pin_gate') }}"
               class="flex items-center justify-center w-9 h-9 rounded-full bg-indigo-100 hover:bg-indigo-200 border-2 border-indigo-300 text-indigo-900 text-base shadow-sm active:scale-90 transition-transform"
               title="Parent Zone (PIN Protected)">
                ⚙️
            </a>
        @endif
    </div>
</div>