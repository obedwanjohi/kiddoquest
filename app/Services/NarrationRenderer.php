<?php

namespace App\Services;

use App\Models\Lesson;

/**
 * Renders dynamic lesson narration text by substituting placeholders such as
 * {child_name}, {lesson_title}, {subject_name}, {level_name}.
 *
 * The engine is intentionally simple and extensible: add new placeholders to
 * contextFromLesson() (or pass your own via the $context array) and they become
 * available immediately. Actual speech synthesis (browser TTS now, ElevenLabs/
 * Polly later) consumes the rendered string + the lesson's selected Voice.
 */
class NarrationRenderer
{
    /** Placeholders the engine understands (for admin hints/validation). */
    public const SUPPORTED = ['child_name', 'lesson_title', 'subject_name', 'level_name'];

    /**
     * Replace {placeholder} tokens in $text using $context.
     * Unknown placeholders are left untouched so nothing silently disappears.
     */
    public function render(?string $text, array $context = []): string
    {
        if ($text === null || $text === '') {
            return '';
        }

        return preg_replace_callback('/\{(\w+)\}/', function ($m) use ($context) {
            $key = $m[1];
            return array_key_exists($key, $context) && $context[$key] !== null && $context[$key] !== ''
                ? (string) $context[$key]
                : $m[0]; // leave the original {token} if we have no value
        }, $text);
    }

    /**
     * Build the placeholder context from a lesson and an optional child name.
     */
    public function contextFromLesson(Lesson $lesson, ?string $childName = null): array
    {
        $subject = $lesson->topic?->subject;

        return [
            'child_name' => $childName,
            'lesson_title' => $lesson->title,
            'subject_name' => $subject?->name,
            'level_name' => $subject?->level?->name,
        ];
    }

    /**
     * Convenience: render a lesson's narration for a given slot ('intro'|'summary').
     */
    public function renderLessonSlot(Lesson $lesson, string $slot, ?string $childName = null): string
    {
        $text = $slot === 'summary' ? $lesson->summary_narration_text : $lesson->intro_narration_text;

        return $this->render($text, $this->contextFromLesson($lesson, $childName));
    }
}
