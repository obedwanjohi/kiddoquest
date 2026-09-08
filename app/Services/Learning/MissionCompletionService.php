<?php

namespace App\Services\Learning;

use App\Models\Child;
use App\Models\ChildDailyStat;
use App\Models\ChildProgress;
use App\Models\ChildQuestionAttempt;
use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\QuizQuestion;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Writes a finished mission into the record: the attempt, the per-question log,
 * the child's progress row, and the stars, coins and streak they earned.
 *
 * Both the website's submit endpoint and the app's sync endpoint go through here,
 * so a mission played on a TV is worth exactly what the same mission is worth in
 * a browser. Passing an event_id makes the whole thing idempotent, which is what
 * lets a device re-send its outbox without paying a child twice.
 */
class MissionCompletionService
{
    /**
     * @param  array{event_id?:string|null,pack_version?:int|null,time_spent?:int,answers?:array,completed_at?:CarbonInterface|null,source?:string}  $context
     * @return array{duplicate:bool,earned_coins:int,net_new_stars:int,total_stars:int,star_coins:int,streak_days:int,attempt_id:int|null}
     */
    public function apply(Child $child, Mission $mission, ScoreResult $result, array $context = []): array
    {
        $eventId = $context['event_id'] ?? null;

        if ($eventId !== null) {
            $existing = MissionAttempt::where('event_id', $eventId)->first();

            if ($existing) {
                return $this->unchanged($child->fresh(), duplicate: true, attemptId: $existing->id);
            }
        }

        $completedAt = $context['completed_at'] ?? now();
        $timeSpent = min(
            (int) ($context['time_spent'] ?? 0),
            (int) config('kiddoquest.session.max_time_spent_seconds', 7200)
        );
        $answers = is_array($context['answers'] ?? null) ? $context['answers'] : [];

        $outcome = DB::transaction(function () use ($child, $mission, $result, $context, $eventId, $completedAt, $timeSpent, $answers) {
            $previousBest = (int) (MissionAttempt::where('child_id', $child->id)
                ->where('mission_id', $mission->id)
                ->max('stars') ?? 0);

            $attempt = MissionAttempt::create(array_filter([
                'child_id'     => $child->id,
                'mission_id'   => $mission->id,
                'event_id'     => $eventId,
                'pack_version' => $context['pack_version'] ?? null,
                'source'       => $context['source'] ?? 'web',
                'score'        => $result->score,
                'total'        => $result->total,
                'stars'        => $result->stars,
                'passed'       => $result->passed,
                'answers'      => $answers,
                'time_spent'   => $timeSpent,
                'completed_at' => $completedAt,
            ], static fn ($value) => $value !== null));

            $this->logQuestionAttempts($child, $mission, $result, $answers, $completedAt);
            $this->upsertProgress($child, $mission, $result, $completedAt);

            $netNewStars = max(0, $result->stars - $previousBest);
            $coins = $this->applyRewards($child, $result, $completedAt, $netNewStars);

            $this->recordDailyStats($child, $result, $completedAt, $timeSpent, $netNewStars, $coins);

            // Badges are decided here, on the server, from what actually happened.
            $badges = app(BadgeService::class)->evaluate($child->fresh(), $mission, $result);

            return ['attempt_id' => $attempt->id, 'net_new_stars' => $netNewStars, 'coins' => $coins, 'badges' => $badges];
        });

        $child->refresh();

        return [
            'duplicate'     => false,
            'earned_coins'  => $outcome['coins'],
            'net_new_stars' => $outcome['net_new_stars'],
            'total_stars'   => (int) $child->total_stars,
            'star_coins'    => (int) $child->star_coins,
            'streak_days'   => (int) ($child->streak_days ?? 1),
            'attempt_id'    => $outcome['attempt_id'],
            'badges'        => $outcome['badges'] ?? [],
        ];
    }

    /**
     * Stars are only ever added for the improvement over the child's own best,
     * so replaying a mission for fun cannot inflate the total.
     */
    protected function applyRewards(Child $child, ScoreResult $result, CarbonInterface $completedAt, int $netNewStars): int
    {
        $config = config('kiddoquest.rewards.coins', []);
        $base = (int) ($config['base'] ?? 10);
        $performance = (int) (($config['per_stars'] ?? [])[$result->stars] ?? 0);

        $localDate = $completedAt->copy()->setTimezone($this->timezone())->toDateString();
        $lastStreak = $child->last_streak_date?->toDateString();
        $streakBonus = 0;

        if (! $lastStreak) {
            $child->streak_days = 1;
            $child->last_streak_date = $localDate;
            $streakBonus = (int) ($config['streak_first_day'] ?? 5);
        } elseif ($lastStreak === $this->yesterdayOf($localDate)) {
            $child->streak_days = (int) ($child->streak_days ?? 1) + 1;
            $child->last_streak_date = $localDate;
            $streakBonus = (int) ($config['streak_continued'] ?? 10);
        } elseif ($lastStreak !== $localDate) {
            $child->streak_days = 1;
            $child->last_streak_date = $localDate;
        }

        $earned = $base + $performance + $streakBonus;

        $child->last_played_at = $completedAt;
        $child->save();

        if ($netNewStars > 0) {
            $child->increment('total_stars', $netNewStars);
        }

        if ($earned > 0) {
            $child->increment('star_coins', $earned);
        }

        return $earned;
    }

    protected function logQuestionAttempts(Child $child, Mission $mission, ScoreResult $result, array $answers, CarbonInterface $completedAt): void
    {
        if ($answers === []) {
            return;
        }

        $bankId = $mission->question_bank_id;
        $known = QuizQuestion::whereIn('id', array_keys($result->perQuestion))->pluck('id')->all();
        $known = array_flip($known);

        foreach ($answers as $answer) {
            if (! is_array($answer)) {
                continue;
            }

            $questionId = (int) ($answer['question_id'] ?? $answer['id'] ?? 0);

            if ($questionId === 0 || ! isset($known[$questionId])) {
                continue;
            }

            try {
                ChildQuestionAttempt::create([
                    'child_id'           => $child->id,
                    'mission_id'         => $mission->id,
                    'question_bank_id'   => $bankId,
                    'question_id'        => $questionId,
                    // The server's own verdict, not the client's claim.
                    'is_correct'         => (bool) ($result->perQuestion[$questionId] ?? false),
                    'time_spent_seconds' => (int) ($answer['time_spent'] ?? round(((int) ($answer['time_ms'] ?? 0)) / 1000)),
                    'attempted_at'       => $completedAt,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to log question attempt', [
                    'child_id'    => $child->id,
                    'question_id' => $questionId,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * One row per child and mission. "completed" is sticky and stars never go down.
     */
    protected function upsertProgress(Child $child, Mission $mission, ScoreResult $result, CarbonInterface $completedAt): void
    {
        $hasLessonId = Schema::hasColumn('child_progress', 'lesson_id');

        $progress = ChildProgress::where('child_id', $child->id)
            ->where(function ($query) use ($mission, $hasLessonId) {
                $query->where('mission_id', $mission->id);
                if ($hasLessonId) {
                    $query->orWhere('lesson_id', $mission->id);
                }
            })
            ->first();

        if (! $progress) {
            $progress = new ChildProgress();
            $progress->child_id = $child->id;
            $progress->mission_id = $mission->id;
            if ($hasLessonId) {
                $progress->lesson_id = $mission->id;
            }
        }

        if ($result->stars > (int) ($progress->stars_earned ?? 0)) {
            $progress->stars_earned = $result->stars;
        }

        if ($result->passed) {
            $progress->status = 'completed';
            $progress->completed_at = $completedAt;
        } elseif ($progress->status !== 'completed') {
            $progress->status = 'in_progress';
        }

        if (! $progress->started_at) {
            $progress->started_at = $completedAt;
        }

        try {
            $progress->save();
        } catch (\Throwable $e) {
            // Lost a race with another device: fold our result into the winner.
            DB::table('child_progress')
                ->where('child_id', $child->id)
                ->where(function ($query) use ($mission, $hasLessonId) {
                    $query->where('mission_id', $mission->id);
                    if ($hasLessonId) {
                        $query->orWhere('lesson_id', $mission->id);
                    }
                })
                ->update([
                    'status'       => $result->passed ? 'completed' : 'in_progress',
                    'stars_earned' => DB::raw($this->greatest('stars_earned', $result->stars)),
                    'completed_at' => $result->passed ? $completedAt : null,
                    'updated_at'   => now(),
                ]);
        }
    }

    protected function recordDailyStats(Child $child, ScoreResult $result, CarbonInterface $completedAt, int $timeSpent, int $netNewStars, int $coins): void
    {
        // Match on a date-time at midnight, not a bare date string. The model
        // casts `day` and therefore stores "2026-09-07 00:00:00"; looking it up
        // with "2026-09-07" found nothing and tried to insert a second row for
        // the same day, which the unique index rightly refused.
        $day = $completedAt->copy()->setTimezone($this->timezone())->startOfDay();

        $stat = ChildDailyStat::firstOrNew(['child_id' => $child->id, 'day' => $day]);

        $stat->seconds_played = (int) $stat->seconds_played + $timeSpent;
        $stat->missions_completed = (int) $stat->missions_completed + 1;
        $stat->missions_passed = (int) $stat->missions_passed + ($result->passed ? 1 : 0);
        $stat->questions = (int) $stat->questions + $result->total;
        $stat->correct = (int) $stat->correct + $result->score;
        $stat->stars_earned = (int) $stat->stars_earned + $netNewStars;
        $stat->coins_earned = (int) $stat->coins_earned + $coins;
        $stat->save();
    }

    protected function unchanged(Child $child, bool $duplicate, ?int $attemptId): array
    {
        return [
            'duplicate'     => $duplicate,
            'earned_coins'  => 0,
            'net_new_stars' => 0,
            'total_stars'   => (int) $child->total_stars,
            'star_coins'    => (int) $child->star_coins,
            'streak_days'   => (int) ($child->streak_days ?? 1),
            'attempt_id'    => $attemptId,
        ];
    }

    protected function timezone(): string
    {
        return (string) config('kiddoquest.timezone', 'Africa/Nairobi');
    }

    /**
     * SQLite spells the scalar maximum MAX(); Postgres and MySQL spell it GREATEST().
     */
    protected function greatest(string $column, int $value): string
    {
        $function = DB::connection()->getDriverName() === 'sqlite' ? 'MAX' : 'GREATEST';

        return "{$function}(COALESCE({$column}, 0), {$value})";
    }

    protected function yesterdayOf(string $date): string
    {
        return \Carbon\CarbonImmutable::parse($date)->subDay()->toDateString();
    }
}
