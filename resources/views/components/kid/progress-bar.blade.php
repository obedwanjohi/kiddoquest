{{--
    KID UI — Progress Bar
    Usage: <x-kid.progress-bar :value="68" />
--}}
@props(['value' => 0, 'label' => null])

<div {{ $attributes->merge(['class' => 'kid-progress-bar w-full']) }}>
    @if($label)
        <div class="flex justify-between items-center mb-1">
            <span class="text-[var(--kid-text-muted)]" style="font-size: var(--kid-text-caption);">{{ $label }}</span>
            <span class="font-bold tabular-nums text-[var(--kid-text)]" style="font-size: var(--kid-text-caption);">{{ $value }}%</span>
        </div>
    @endif
    <div class="w-full bg-[var(--kid-border)] rounded-full overflow-hidden" style="height: 12px;">
        <div class="progress-fill h-full rounded-full bg-[var(--kid-primary)] transition-all duration-500 ease-out"
             style="width: {{ min(100, max(0, $value)) }}%;">
        </div>
    </div>
</div>