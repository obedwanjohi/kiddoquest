<?php

namespace App\Services\Learning;

use App\Models\Mission;
use App\Models\QuizQuestion;
use App\Services\Content\QuestionNormalizer;

/**
 * Re-derives score, stars and pass/fail from the answers a device sends.
 *
 * The browser and the app both compute these numbers for instant feedback, but
 * what gets written to the database is always what this class decides. The Dart
 * implementation in mobile/lib/core/scoring mirrors it rule for rule, and both
 * are checked against the shared fixtures in fixtures/scoring/.
 */
class MissionScoringService
{
    /**
     * Score an attempt against the mission's questions as they exist in the
     * database. Pass $questionDefs to score against a pinned pack version instead.
     *
     * @param  array<int,array>  $answers  the answers[] array from mission_completed
     * @param  array<int,array>|null  $questionDefs  normalized question definitions
     */
    public function scoreMission(Mission $mission, array $answers, ?array $questionDefs = null): ScoreResult
    {
        $definitions = $questionDefs ?? $this->definitionsForMission($mission, $answers);

        return $this->score(
            $answers,
            $definitions,
            (int) ($mission->pass_threshold_percent ?: config('kiddoquest.session.pass_threshold_percent', 60)),
            $this->sessionCap($mission),
        );
    }

    /**
     * @param  array<int,array>  $answers
     * @param  array<int,array>  $definitions  keyed by question id, or a plain list
     */
    public function score(array $answers, array $definitions, int $passThreshold = 60, ?int $cap = null): ScoreResult
    {
        $byId = [];
        foreach ($definitions as $key => $definition) {
            $id = (int) ($definition['id'] ?? $key);
            $byId[$id] = $definition;
        }

        $score = 0;
        $total = 0;
        $perQuestion = [];
        $notes = [];
        $seen = [];

        foreach ($answers as $answer) {
            if (! is_array($answer)) {
                continue;
            }

            $questionId = (int) ($answer['question_id'] ?? $answer['id'] ?? 0);

            if ($questionId === 0 || ! isset($byId[$questionId])) {
                $notes[$questionId] = 'unknown_question';
                continue;
            }

            // A question only ever counts once, however many times it is sent.
            if (isset($seen[$questionId])) {
                $notes[$questionId] = 'duplicate';
                continue;
            }
            $seen[$questionId] = true;

            if ($cap !== null && $total >= $cap) {
                $notes[$questionId] = 'over_session_cap';
                continue;
            }

            $correct = $this->isCorrect($byId[$questionId], $answer);

            if ($correct === null) {
                $notes[$questionId] = 'not_scored';
                continue;
            }

            $total++;
            $perQuestion[$questionId] = $correct;

            if ($correct) {
                $score++;
            }
        }

        $percentage = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        return new ScoreResult(
            score: $score,
            total: $total,
            stars: self::starsFor($score, $total),
            passed: $total > 0 && $percentage >= $passThreshold,
            percentage: $percentage,
            perQuestion: $perQuestion,
            notes: $notes,
        );
    }

    /**
     * Star thresholds, identical to calculateStars() in public/js/kid/quiz-engine.js.
     */
    public static function starsFor(int $score, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        $pct = ($score / $total) * 100;
        $t = config('kiddoquest.rewards.star_thresholds', ['three' => 90, 'two' => 60, 'one' => 30]);

        return match (true) {
            $pct >= $t['three'] => 3,
            $pct >= $t['two']   => 2,
            $pct >= $t['one']   => 1,
            default             => 0,
        };
    }

    /**
     * Decide one answer.
     *
     * @return bool|null  null when the question is not scored at all
     */
    public function isCorrect(array $question, array $answer): ?bool
    {
        $type = QuestionNormalizer::typeSlug($question['type'] ?? null);
        $response = $this->responseFor($answer);
        $options = $question['options'] ?? [];

        return match ($type) {
            'multiple_choice', 'listen_choose', 'true_false', 'fill_blank', 'count_objects', 'pattern'
                => $this->scoreSingleChoice($options, $response),
            'matching'      => $this->scoreMatching($options, $response),
            'drag_sort'     => $this->scoreDragSort($options, $question, $response),
            'drag_sequence' => $this->scoreSequence($options, $response),
            'memory_match'  => $this->scoreMemoryMatch($options, $response),
            'spot_find'     => $this->scoreSpotFind($question, $response),
            'speak_repeat'  => $this->scoreSpeakRepeat($response),
            // Tracing is a practice activity on the web too: doing it is passing it.
            'tracing'       => true,
            default         => $this->scoreSingleChoice($options, $response),
        };
    }

    /**
     * The client may send {response:{...}} or put the fields at the top level.
     */
    protected function responseFor(array $answer): array
    {
        if (isset($answer['response']) && is_array($answer['response'])) {
            return $answer['response'];
        }

        return $answer;
    }

    protected function scoreSingleChoice(array $options, array $response): ?bool
    {
        $chosen = $response['option_id'] ?? $response['selected'] ?? null;

        if ($chosen === null || $chosen === '') {
            return false;
        }

        foreach ($options as $option) {
            if ((int) ($option['id'] ?? 0) === (int) $chosen) {
                return (bool) ($option['is_correct'] ?? false);
            }
        }

        return false;
    }

    /**
     * Options carry a shared match_key such as "🐶 Puppy"; a pair is right when
     * both sides carry the same key.
     */
    protected function scoreMatching(array $options, array $response): bool
    {
        $pairs = $response['pairs'] ?? [];

        if (! is_array($pairs) || $pairs === []) {
            return false;
        }

        $keys = [];
        foreach ($options as $option) {
            $keys[(int) ($option['id'] ?? 0)] = $this->normalizeKey($option['match_key'] ?? null);
        }

        $expected = count(array_filter(array_unique(array_values($keys))));
        $matched = 0;

        foreach ($pairs as $pair) {
            $left = null;
            $right = null;

            if (is_array($pair) && array_is_list($pair) && count($pair) >= 2) {
                [$left, $right] = $pair;
            } elseif (is_array($pair)) {
                $left = $pair['left'] ?? $pair['left_id'] ?? null;
                $right = $pair['right'] ?? $pair['right_id'] ?? null;
            }

            if ($left === null || $right === null) {
                return false;
            }

            $leftKey = $keys[(int) $left] ?? null;
            $rightKey = $keys[(int) $right] ?? null;

            if ($leftKey === null || $leftKey === '' || $leftKey !== $rightKey) {
                return false;
            }

            $matched++;
        }

        return $expected === 0 ? false : $matched >= $expected;
    }

    /**
     * placements maps option id to the bucket the child dropped it in; the right
     * bucket is the option's own match_key (or its bucket in scoring_config).
     */
    protected function scoreDragSort(array $options, array $question, array $response): bool
    {
        $placements = $response['placements'] ?? $response['buckets'] ?? [];

        if (! is_array($placements) || $placements === []) {
            return false;
        }

        $expected = [];
        foreach ($options as $option) {
            $expected[(int) ($option['id'] ?? 0)] = $this->normalizeKey($option['match_key'] ?? null);
        }

        $categories = $question['scoring_config']['categories'] ?? null;

        foreach ($expected as $optionId => $bucket) {
            if ($bucket === '' && is_array($categories)) {
                $bucket = $this->normalizeKey($categories[$optionId] ?? null);
            }

            $placed = $this->normalizeKey($placements[$optionId] ?? $placements[(string) $optionId] ?? null);

            if ($bucket === '' || $placed !== $bucket) {
                return false;
            }
        }

        return true;
    }

    /** The correct order is the options ordered by sort_order. */
    protected function scoreSequence(array $options, array $response): bool
    {
        $order = $response['order'] ?? $response['slots'] ?? [];

        if (! is_array($order) || $order === []) {
            return false;
        }

        $sorted = $options;
        usort($sorted, fn ($a, $b) => ((int) ($a['sort_order'] ?? 0)) <=> ((int) ($b['sort_order'] ?? 0)));
        $expected = array_map(static fn ($option) => (int) ($option['id'] ?? 0), $sorted);

        $given = array_map(static fn ($value) => (int) (is_array($value) ? ($value['id'] ?? 0) : $value), $order);

        return $expected === $given;
    }

    protected function scoreMemoryMatch(array $options, array $response): bool
    {
        $found = (int) ($response['pairs_found'] ?? 0);

        $keys = array_filter(array_unique(array_map(
            fn ($option) => $this->normalizeKey($option['match_key'] ?? null),
            $options
        )));

        $expected = count($keys) > 0 ? count($keys) : (int) floor(count($options) / 2);

        return $expected > 0 && $found >= $expected;
    }

    /**
     * Every hotspot has to be hit. Coordinates are percentages of the image, so
     * the tolerance is a percentage too.
     */
    protected function scoreSpotFind(array $question, array $response): bool
    {
        $hotspots = $question['metadata']['hotspots'] ?? [];
        $hits = $response['hits'] ?? [];

        if (! is_array($hotspots) || $hotspots === [] || ! is_array($hits)) {
            return false;
        }

        $radius = (float) ($question['metadata']['hotspot_radius'] ?? $question['scoring_config']['hotspot_radius'] ?? 12);

        foreach ($hotspots as $spot) {
            $sx = (float) ($spot['x'] ?? -1);
            $sy = (float) ($spot['y'] ?? -1);
            $hit = false;

            foreach ($hits as $candidate) {
                if (! is_array($candidate)) {
                    continue;
                }

                $dx = ((float) ($candidate['x'] ?? 999)) - $sx;
                $dy = ((float) ($candidate['y'] ?? 999)) - $sy;

                if (sqrt(($dx * $dx) + ($dy * $dy)) <= $radius) {
                    $hit = true;
                    break;
                }
            }

            if (! $hit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Speech recognition is not available on every device, and a three-year-old
     * cannot be told to buy a better phone. A recognized word is correct; so is
     * an honest practice attempt. Only skipping is wrong.
     */
    protected function scoreSpeakRepeat(array $response): bool
    {
        $mode = strtolower((string) ($response['mode'] ?? 'practice'));

        return match ($mode) {
            'recognized' => true,
            'skipped'    => false,
            default      => true,
        };
    }

    protected function normalizeKey(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return mb_strtolower(trim((string) $value));
    }

    /**
     * How many questions this mission may score, from the child's level cap and
     * the mission's own setting.
     */
    public function sessionCap(Mission $mission): int
    {
        $missionCap = (int) ($mission->questions_per_session ?: 0);

        return max(1, $missionCap ?: (int) config('kiddoquest.session.questions_default', 8));
    }

    /**
     * Load definitions for exactly the questions that were answered, so a long
     * bank does not have to be hydrated to score six answers.
     *
     * @param  array<int,array>  $answers
     * @return array<int,array>
     */
    public function definitionsForMission(Mission $mission, array $answers): array
    {
        $ids = [];
        foreach ($answers as $answer) {
            if (is_array($answer)) {
                $id = (int) ($answer['question_id'] ?? $answer['id'] ?? 0);
                if ($id > 0) {
                    $ids[$id] = true;
                }
            }
        }

        if ($ids === []) {
            return [];
        }

        $questions = QuizQuestion::with(['options', 'quizType'])
            ->whereIn('id', array_keys($ids))
            ->get();

        $definitions = [];
        foreach ($questions as $question) {
            $definitions[(int) $question->id] = QuestionNormalizer::fromModel($question);
        }

        return $definitions;
    }
}
