{{--
    KID UI — Avatar Card (Child Profile Selection)
    Usage: <x-kid.avatar-card :child="$child" />
--}}
@props(['child' => null])

@php
    $name = is_object($child) ? $child->name : ($child['name'] ?? 'Adventurer');
    $avatar = is_object($child) ? ($child->avatar_identifier ?? $child->avatar ?? 'lion') : ($child['avatar'] ?? 'lion');
    $age = is_object($child) ? $child->age ?? null : ($child['age'] ?? null);
    $level = is_object($child) ? ($child->recommended_level ?? $child->level ?? 'PP1') : ($child['level'] ?? 'PP1');
    $stars = is_object($child) ? ($child->total_stars ?? 0) : ($child['stars'] ?? 0);
    $progress = is_object($child) ? ($child->overall_progress ?? 0) : ($child['progress'] ?? 0);
    $childId = is_object($child) ? $child->id : ($child['id'] ?? null);

    $avatarEmojis = [
        'lion' => '🦁', 'elephant' => '🐘', 'giraffe' => '🦒',
        'panda' => '🐼', 'tiger' => '🐯', 'monkey' => '🐵',
        'rabbit' => '🐰', 'fox' => '🦊', 'bear' => '🐻',
    ];
    $avatarEmoji = $avatarEmojis[$avatar] ?? '🦁';
@endphp

@php
    $profileUrl = '#';
    if ($childId && \Illuminate\Support\Facades\Route::has('kids.profiles.select')) {
        try { $profileUrl = route('kids.profiles.select', $childId); } catch (\Exception $e) {}
    }
@endphp

<a href="{{ $profileUrl }}"
   class="kid-avatar-card block bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-xl)]
          shadow-[var(--kid-shadow-medium)] p-6 text-center
          transition-all duration-300
          hover:scale-105 hover:shadow-[var(--kid-shadow-popup)]
          kid-bounce-in">

    {{-- Avatar --}}
    <div class="text-6xl mb-3">{{ $avatarEmoji }}</div>

    {{-- Name --}}
    <h3 class="font-black text-[var(--kid-text)] mb-1"
        style="font-family: var(--kid-font-heading); font-size: var(--kid-text-title);">
        {{ $name }}
    </h3>

    {{-- Age & Level --}}
    <p class="text-[var(--kid-text-muted)] mb-4" style="font-size: var(--kid-text-caption);">
        @if($age) Age {{ $age }} · @endif{{ $level }}
    </p>

    {{-- Stars --}}
    <div class="inline-flex items-center gap-1.5 bg-[var(--kid-bg)] rounded-full px-4 py-2 mb-3">
        <span class="text-lg">⭐</span>
        <span class="font-black tabular-nums text-[var(--kid-encourage-dark)]"
              style="font-size: var(--kid-text-counter);">{{ $stars }}</span>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-[var(--kid-border)] rounded-full overflow-hidden" style="height: 10px;">
        <div class="h-full rounded-full bg-[var(--kid-primary)] transition-all duration-500"
             style="width: {{ min(100, max(0, $progress)) }}%;"></div>
    </div>
    <p class="text-[var(--kid-text-muted)] mt-1.5" style="font-size: var(--kid-text-caption);">
        {{ $progress }}% complete
    </p>
</a>