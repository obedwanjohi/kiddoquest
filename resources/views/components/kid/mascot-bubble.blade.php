{{--
    KID UI — Mascot Bubble (Leo's speech)
    Usage: <x-kid.mascot-bubble text="What color is the sky?" :name="Emma" />
    JS adds typewriter effect when .kid-typewriter class is present on text span.
--}}
@props([
    'text' => '',
    'name' => null,
    'mascot' => '🦁',
    'position' => 'bottom-left', // bottom-left | top-center
])

<div class="kid-mascot-bubble flex items-end gap-3 {{ $position === 'top-center' ? 'flex-col items-center' : '' }}"
     data-mascot-bubble>
    <div class="relative max-w-md bg-white rounded-[var(--kid-radius-lg)]
                shadow-[var(--kid-shadow-medium)] p-4
                {{ $position === 'top-center' ? 'text-center' : '' }}">
        {{-- Tail --}}
        <span class="absolute bottom-0 left-8 translate-y-1/2 w-0 h-0
                     border-l-[12px] border-l-transparent
                     border-r-[12px] border-r-transparent
                     border-t-[16px] border-t-white
                     {{ $position === 'top-center' ? 'hidden' : '' }}"></span>

        @if($name)
            <span class="block font-bold text-[var(--world-primary)] mb-1"
                  style="font-size: var(--kid-text-caption);">Leo says:</span>
        @endif
        <span class="kid-typewriter block text-[var(--kid-text)] font-semibold"
              style="font-size: var(--kid-text-body); line-height: var(--kid-leading-normal);">
            {{ $text }}
        </span>
    </div>
    <span class="text-5xl kid-float">{{ $mascot }}</span>
</div>