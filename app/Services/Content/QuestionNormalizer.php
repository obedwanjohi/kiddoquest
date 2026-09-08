<?php

namespace App\Services\Content;

use App\Models\QuizQuestion;

/**
 * Turns a database question row into the flat shape used by everything else:
 * the content pack, the scoring service and the Flutter renderers.
 *
 * The website builds this shape inline inside engine.blade.php; this class is
 * the single definition of it so the pack, the server scorer and the app can
 * never drift apart.
 */
class QuestionNormalizer
{
    /**
     * Type slugs the renderers know, keyed by every spelling the database uses.
     * The web engine does the same rewriting inline (hyphens to underscores,
     * complete-pattern to pattern).
     */
    public const TYPE_ALIASES = [
        'multiple-choice'  => 'multiple_choice',
        'multiple_choice'  => 'multiple_choice',
        'tap_answer'       => 'multiple_choice',
        'tap-answer'       => 'multiple_choice',
        'listen-choose'    => 'listen_choose',
        'listen_choose'    => 'listen_choose',
        'true-false'       => 'true_false',
        'true_false'       => 'true_false',
        'matching'         => 'matching',
        'drag-sort'        => 'drag_sort',
        'drag_sort'        => 'drag_sort',
        'drag-sequence'    => 'drag_sequence',
        'drag_sequence'    => 'drag_sequence',
        'speak-repeat'     => 'speak_repeat',
        'speak_repeat'     => 'speak_repeat',
        'fill-blank'       => 'fill_blank',
        'fill_blank'       => 'fill_blank',
        'count-objects'    => 'count_objects',
        'count_objects'    => 'count_objects',
        'complete-pattern' => 'pattern',
        'complete_pattern' => 'pattern',
        'pattern'          => 'pattern',
        'memory-match'     => 'memory_match',
        'memory_match'     => 'memory_match',
        'tracing'          => 'tracing',
        'spot-find'        => 'spot_find',
        'spot_find'        => 'spot_find',
    ];

    /** Every type a renderer exists for, in the app. */
    public const TYPES = [
        'multiple_choice', 'true_false', 'matching', 'drag_sort', 'drag_sequence',
        'listen_choose', 'speak_repeat', 'fill_blank', 'count_objects', 'pattern',
        'memory_match', 'tracing', 'spot_find',
    ];

    public static function typeSlug(?string $rawType, ?string $quizTypeSlug = null): string
    {
        $raw = trim((string) ($rawType ?: $quizTypeSlug ?: 'multiple_choice'));
        $key = strtolower($raw);

        if (isset(self::TYPE_ALIASES[$key])) {
            return self::TYPE_ALIASES[$key];
        }

        $underscored = str_replace('-', '_', $key);

        return self::TYPE_ALIASES[$underscored] ?? $underscored;
    }

    /**
     * @return array{id:int,type:string,prompt:?string,narration:array,image:?string,audio:?string,hint:?string,explanation:?string,points:int,difficulty:?string,scoring_config:array,metadata:array,options:array}
     */
    public static function fromModel(QuizQuestion $question): array
    {
        $type = self::typeSlug($question->type, $question->quizType?->slug);

        $narrationAudio = null;
        if ($question->relationLoaded('narration') && $question->narration) {
            $narrationAudio = $question->narration->audio_url ?? null;
        }

        return [
            'id'         => (int) $question->id,
            'type'       => $type,
            'type_name'  => $question->quizType->name ?? 'Question',
            'type_icon'  => $question->quizType->icon ?? '❓',
            'prompt'     => $question->prompt,
            'narration'  => [
                'text'  => $question->narration_text ?: $question->prompt,
                'audio' => $question->prompt_audio_url ?: $narrationAudio,
            ],
            'image'          => $question->prompt_image_url,
            'audio'          => $question->prompt_audio_url ?: $narrationAudio,
            'hint'           => $question->hint,
            'explanation'    => $question->explanation,
            'points'         => (int) ($question->points ?: 1),
            'difficulty'     => $question->difficulty,
            'cbc_outcome'    => $question->cbc_outcome_code,
            'scoring_config' => self::asArray($question->scoring_config),
            'metadata'       => self::asArray($question->metadata),
            'options'        => $question->options
                ->sortBy('sort_order')
                ->values()
                ->map(fn ($option) => [
                    'id'           => (int) $option->id,
                    'text'         => $option->text_value,
                    'image'        => $option->image_url,
                    'audio'        => $option->audio_url,
                    'is_correct'   => (bool) $option->is_correct,
                    'content_type' => $option->content_type,
                    'match_key'    => $option->match_key,
                    'sort_order'   => (int) $option->sort_order,
                ])
                ->all(),
        ];
    }

    /**
     * Strip the answer key from a question before it is handed to a client that
     * has not earned it. Content packs keep is_correct (the child plays offline
     * and the server re-scores anyway), so this is only for live endpoints.
     */
    public static function withoutAnswers(array $question): array
    {
        $question['options'] = array_map(static function (array $option) {
            unset($option['is_correct'], $option['match_key']);

            return $option;
        }, $question['options'] ?? []);

        return $question;
    }

    protected static function asArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
