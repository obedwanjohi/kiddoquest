{{--
    KID UI — Primary Button
    Usage: <x-kid.button route="kids.map" label="Let's Go!" />
    Or:    <x-kid.button type="submit" label="Save" />
--}}
@props([
    'label' => null,
    'route' => null,
    'type' => 'button',
    'icon' => null,
    'disabled' => false,
])

@php
    $tag = $route ? 'a' : 'button';
    $href = $route ? route($route) : null;
@endphp

<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    @disabled($disabled)
    class="kid-btn inline-flex items-center justify-center gap-2 font-bold text-white
           px-8 py-4 rounded-[var(--kid-radius-md)]
           bg-[var(--world-primary)]
           shadow-[var(--kid-shadow-3d)]
           active:scale-[0.96] active:shadow-[var(--kid-shadow-3d-pressed)]
           transition-transform
           disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none
           select-none touch-manipulation"
    style="font-family: var(--kid-font-heading); font-size: var(--kid-text-mission);"
>
    @if($icon)<span class="text-2xl">{{ $icon }}</span>@endif
    {{ $label ?? $slot }}
</{{ $tag }}>