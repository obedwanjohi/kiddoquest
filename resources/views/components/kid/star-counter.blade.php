{{--
    KID UI — Star Counter
    Usage: <x-kid.star-counter :count="$child->total_stars" />
    Animated via JS by toggling .kid-star-bump class.
--}}
@props(['count' => 0])

<div class="kid-star-counter inline-flex items-center gap-1.5
     bg-white/90 backdrop-blur rounded-full px-4 py-2
     shadow-[var(--kid-shadow-soft)]" data-star-counter>
    <span class="text-xl" style="filter: drop-shadow(0 1px 2px rgba(0,0,0,0.1));">⭐</span>
    <span class="font-black tabular-nums text-[var(--kid-encourage-dark)]"
          style="font-size: var(--kid-text-counter); font-family: var(--kid-font-heading);"
          data-star-count>{{ $count }}</span>
</div>