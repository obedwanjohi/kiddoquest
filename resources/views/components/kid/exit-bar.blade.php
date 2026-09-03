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
    $activeChild = null;
    $remainingMinutes = 999;
    if (session('active_child_id')) {
        $activeChild = \App\Models\Child::find(session('active_child_id'));
        if ($activeChild) {
            $remainingMinutes = $activeChild->remaining_time_minutes;
            $coins = $coins ?? $activeChild->star_coins;
        }
    }
    $coins = $coins ?? 0;
@endphp

<div class="kid-exit-bar fixed top-0 left-0 right-0 z-50
            flex items-center justify-between px-2.5 sm:px-4 py-2 sm:py-2.5
            bg-white/95 backdrop-blur-md border-b-2 border-slate-100 shadow-sm">

    {{-- Left: Back/Home + Shop Pouch --}}
    @php
        $isMapPage = request()->routeIs('kids.map');
        $homeUrl = route('kids.profiles');
        $mapUrl = route('kids.map');
    @endphp
    <div class="flex items-center gap-1.5 sm:gap-2">
        @if($isMapPage)
            <a href="{{ $homeUrl }}"
               class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-xl sm:rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-sm active:translate-y-0.5 transition-all"
               title="Back to Profiles">
                <span class="text-sm sm:text-base">🏠</span>
                <span class="hidden sm:inline">Home</span>
            </a>
        @else
            <a href="{{ $mapUrl }}"
               class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-xl sm:rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-sm active:translate-y-0.5 transition-all"
               title="Back to Map">
                <span class="text-sm sm:text-base">🗺️</span>
                <span class="hidden sm:inline">Map</span>
            </a>
        @endif

        {{-- Premium Shop Pouch Button --}}
        @if(\Illuminate\Support\Facades\Route::has('kids.shop'))
            <a href="{{ route('kids.shop') }}"
               class="flex items-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-xl sm:rounded-2xl bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black text-xs border border-amber-300 shadow-sm active:translate-y-0.5 transition-all cursor-pointer"
               title="Star Shop">
                <span class="text-sm sm:text-base">🪙</span>
                <span class="font-black text-xs sm:text-sm text-slate-950">{{ number_format($coins) }}</span>
                <span class="hidden md:inline bg-slate-950/15 text-slate-900 px-1 py-0.5 rounded text-[10px] uppercase font-black">Shop</span>
            </a>
        @endif
    </div>

    {{-- Center: Title + Screen-Time Remaining Pill --}}
    <div class="flex items-center gap-2">
        @if($title)
            <h1 class="font-heading font-black text-slate-800 text-xs sm:text-sm md:text-base text-center px-1 truncate max-w-[120px] sm:max-w-xs">
                {{ $title }}
            </h1>
        @endif

        @if($activeChild && ($activeChild->daily_time_limit_minutes ?? 0) > 0)
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full font-black text-[10px] sm:text-xs shadow-2xs border transition-colors {{ $remainingMinutes <= 5 ? 'bg-rose-50 border-rose-300 text-rose-700 animate-pulse' : ($remainingMinutes <= 10 ? 'bg-amber-50 border-amber-300 text-amber-900' : 'bg-emerald-50 border-emerald-300 text-emerald-900') }}"
                 title="Daily Learning Time Remaining">
                <span class="text-xs">⏳</span>
                <span>{{ $remainingMinutes > 0 ? $remainingMinutes . 'm Left' : 'Time Up 🎵' }}</span>
            </div>
        @endif
    </div>

    {{-- Right: Stars + Parent Lock Settings --}}
    <div class="flex items-center gap-1.5 sm:gap-2">
        <x-kid.star-counter :count="$stars" />
        
        @if(\Illuminate\Support\Facades\Route::has('parent.pin_gate'))
            <a href="{{ route('parent.pin_gate') }}"
               class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-xl sm:rounded-full bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 text-sm sm:text-base shadow-xs active:scale-90 transition-transform"
               title="Parent Zone (PIN Protected)">
                ⚙️
            </a>
        @endif
    </div>
</div>