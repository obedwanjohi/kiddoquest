{{--
    KID UI — Answer Card (for quiz answers)
    Usage: <x-kid.answer-card :option="$option" :index="1" />
    Or:    <x-kid.answer-card emoji="🍎" label="Apple" />
    Or:    <x-kid.answer-card text="Blue" state="correct" />
    JS will toggle .is-correct / .is-incorrect classes for feedback states.
--}}
@props([
    'option' => null,       // QuestionOption model or array
    'index' => 0,
    'letter' => null,       // A, B, C, D override
    'emoji' => null,        // Direct emoji override
    'label' => null,        // Direct text label
    'text' => null,         // Alias for label
    'selected' => false,
    'state' => null,        // null | correct | wrong
])

@php
    $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    $letterLabel = $letter ?? ($letters[$index] ?? '?');

    // Pull from option object/array or direct props
    $optionText = $label ?? $text;
    $optionEmoji = $emoji;
    $optionId = null;
    $optionImage = null;

    if ($option) {
        $optionText = $optionText ?? (is_object($option) ? $option->option_text : ($option['text'] ?? $option['label'] ?? null));
        $optionId = is_object($option) ? $option->id : ($option['id'] ?? null);
        $optionImage = is_object($option) ? ($option->image_url ?? null) : ($option['image'] ?? null);
        $optionEmoji = $optionEmoji ?? (is_object($option) ? ($option->metadata['emoji'] ?? null) : ($option['emoji'] ?? null));
    }

    // State classes
    $stateClasses = '';
    $badgeClass = 'bg-[var(--world-primary-light)] text-white';
    if ($state === 'correct') {
        $stateClasses = 'border-[var(--kid-success)] bg-[var(--kid-success-light)]';
        $badgeClass = 'bg-[var(--kid-success)] text-white';
    } elseif ($state === 'wrong') {
        $stateClasses = 'border-[var(--kid-danger)] bg-[var(--kid-danger-light)]';
        $badgeClass = 'bg-[var(--kid-danger)] text-white';
    } elseif ($selected) {
        $stateClasses = 'border-[var(--world-primary)] bg-[var(--world-primary-light)]';
    }
@endphp

<button
    type="button"
    data-answer-card
    data-option-id="{{ $optionId }}"
    class="kid-answer-card w-full flex items-center gap-4 p-4
           bg-[var(--kid-bg-card)] rounded-[var(--kid-radius-lg)]
           border-[3px] border-[var(--kid-border)]
           shadow-[var(--kid-shadow-soft)]
           active:scale-[0.97]
           transition-all duration-100
           text-left select-none touch-manipulation
           {{ $stateClasses }}"
    style="min-height: var(--kid-touch-large);"
>
    {{-- Letter Badge --}}
    <span class="kid-answer-letter flex-shrink-0 w-12 h-12 rounded-full
                 flex items-center justify-center font-black text-lg
                 {{ $badgeClass }}">
        {{ $letterLabel }}
    </span>

    {{-- Option Content --}}
    <span class="flex-1 flex items-center gap-2">
        @if($optionEmoji)
            <span class="text-3xl">{{ $optionEmoji }}</span>
        @endif
        @if($optionImage)
            <img src="{{ $optionImage }}" alt="" class="h-12 w-12 rounded-[var(--kid-radius-sm)] object-cover">
        @endif
        @if($optionText)
            <span class="font-semibold text-[var(--kid-text)]" style="font-size: var(--kid-text-answer);">
                {{ $optionText }}
            </span>
        @endif
    </span>

    {{-- Checkmark / Cross (shown based on state) --}}
    @if($state === 'correct')
        <span class="flex-shrink-0 text-2xl text-[var(--kid-success)]">✓</span>
    @elseif($state === 'wrong')
        <span class="flex-shrink-0 text-2xl text-[var(--kid-danger)]">✗</span>
    @else
        <span class="kid-answer-check hidden flex-shrink-0 text-2xl text-[var(--kid-success)]">✓</span>
    @endif
</button>