{{--
    KID UI — Mission Card (World Trail)
    Usage: <x-kid.mission-card :mission="$lesson" :stars="2" :maxStars="3" />
--}}
@props([
    'mission' => null,
    'stars' => 0,
    'maxStars' => 3,
    'locked' => false,
])

@php
    $title = is_object($mission) ? ($mission->title ?? $mission->name ?? 'Mission') : ($mission['title'] ?? 'Mission');
    $icon = is_object($mission) ? ($mission->icon ?? '🎯') : ($mission['icon'] ?? '🎯');
    $missionId = is_object($mission) ? ($mission->id ?? null) : ($mission['id'] ?? null);
@endphp

<div class="kid-mission-card bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)]
             shadow-[var(--kid-shadow-soft)] p-5
             {{ $locked ? 'opacity-60' : 'hover:shadow-[var(--kid-shadow-medium)]' }}
             transition-all duration-300 kid-fade-up">

    <div class="flex items-start gap-3 mb-3">
        <span class="text-4xl">{{ $locked ? '🔒' : $icon }}</span>
        <div class="flex-1">
            <h4 class="font-bold text-[var(--kid-text)]"
                style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
                {{ $title }}
            </h4>
            <div class="flex gap-0.5 mt-1">
                @for($i = 0; $i < $maxStars; $i++)
                    <span class="text-lg {{ $i < $stars ? '' : 'opacity-25 grayscale' }}">⭐</span>
                @endfor
            </div>
        </div>
    </div>

    @php
        $missionUrl = '#';
        if (!$locked && $missionId && \Illuminate\Support\Facades\Route::has('kids.lesson')) {
            try { $missionUrl = route('kids.lesson', $missionId); } catch (\Exception $e) {}
        }
    @endphp

    @if(!$locked)
        <button class="kid-btn w-full font-bold text-white py-2.5 rounded-[var(--kid-radius-md)]
                       bg-[var(--world-primary)] shadow-[var(--kid-shadow-3d)]
                       active:scale-95 transition-transform touch-manipulation"
                style="font-family: var(--kid-font-heading); font-size: var(--kid-text-body);"
                onclick="window.location.href='{{ $missionUrl }}'">
            ▶ Start Mission
        </button>
    @endif
</div>