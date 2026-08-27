{{--
    KID UI — Secondary Button
    Usage: <x-kid.secondary-button route="kids.map" label="Back to Map" />
--}}
@props([
    'label' => null,
    'route' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $tag = $route ? 'a' : 'button';
    $href = $route ? route($route) : null;
@endphp

<{{ $tag }}
    @if($tag === 'a') href="{{ $href }}" @else type="{{ $type }}" @endif
    class="kid-btn-secondary inline-flex items-center justify-center gap-2 font-bold
           px-6 py-3 rounded-[var(--kid-radius-md)]
           bg-white text-[var(--world-primary)]
           border-2 border-[var(--world-primary)]
           shadow-[var(--kid-shadow-soft)]
           active:scale-[0.96]
           transition-transform
           select-none touch-manipulation"
    style="font-family: var(--kid-font-heading); font-size: var(--kid-text-body);"
>
    @if($icon)<span class="text-xl">{{ $icon }}</span>@endif
    {{ $label ?? $slot }}
</{{ $tag }}>