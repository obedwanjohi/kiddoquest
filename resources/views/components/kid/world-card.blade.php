{{--
    KID UI — World Card (Adventure Map)
    Usage: <x-kid.world-card :world="$world" :progress="$progress" />
--}}
@props([
    'world' => null,
    'progress' => 0,
    'locked' => false,
])

@php
    $worldId = is_object($world) ? $world->id : ($world['id'] ?? null);
    $name = is_object($world) ? $world->name : ($world['name'] ?? 'Unknown');
    $description = is_object($world) ? $world->description : ($world['description'] ?? '');
    $icon = is_object($world) ? ($world->icon ?? $world->theme ?? '🌍') : ($world['icon'] ?? '🌍');
    $theme = is_object($world) ? ($world->theme ?? 'forest') : ($world['theme'] ?? 'forest');
    $url = '#';
    if (!$locked && $worldId && \Illuminate\Support\Facades\Route::has('kids.world')) {
        try { $url = route('kids.world', $worldId); } catch (\Exception $e) {}
    }
@endphp

<a href="{{ $url }}"
   class="kid-world-card block bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)]
          shadow-[var(--kid-shadow-medium)] overflow-hidden
          transition-all duration-300
          {{ $locked ? 'opacity-60 cursor-not-allowed' : 'hover:scale-105 hover:shadow-[var(--kid-shadow-popup)]' }}
          kid-bounce-in"
   style="border-top: 8px solid var(--world-primary);">

    {{-- Icon Area --}}
    <div class="flex items-center justify-center py-8 world-{{ $theme }}"
         style="background: linear-gradient(135deg, var(--world-gradient-from), var(--world-gradient-to));">
        @if($locked)
            <span class="text-6xl opacity-50">🔒</span>
        @else
            <span class="text-6xl">{{ $icon }}</span>
        @endif
    </div>

    {{-- Info --}}
    <div class="p-5">
        <h3 class="font-black text-[var(--kid-text)] mb-1"
            style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);">
            {{ $name }}
        </h3>
        <p class="text-[var(--kid-text-muted)] mb-4"
           style="font-size: var(--kid-text-caption);">{{ $description }}</p>

        @if(!$locked)
            <div class="bg-[var(--kid-bg)] rounded-full px-3 py-1.5 inline-flex items-center gap-1.5">
                <span class="text-sm">⭐</span>
                <span class="font-bold tabular-nums text-[var(--kid-text)]" style="font-size: var(--kid-text-caption);">
                    {{ $progress }}% complete
                </span>
            </div>
        @endif
    </div>
</a>