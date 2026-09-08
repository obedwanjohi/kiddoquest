<?php

namespace App\Services\Learning;

use App\Models\Child;
use App\Models\ChildDailyStat;
use App\Models\ChildProgress;
use App\Models\ChildQuestionAttempt;
use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\Subject;
use Illuminate\Support\Carbon;

/**
 * What a parent sees about their child.
 *
 * Everything here comes from the projections rather than being recomputed from
 * events, which is what keeps the dashboard cheap enough to open often. Two
 * things the website got wrong are fixed: learning time is real rather than a
 * hard-coded string, and the mistakes list actually lists mistakes.
 */
class ParentReportService
{
    public function build(Child $child, int $days = 7): array
    {
        $since = Carbon::now($this->timezone())->subDays($days)->startOfDay();

        return [
            'child'      => $this->childSummary($child),
            'range_days' => $days,
            'overview'   => $this->overview($child, $since, $days),
            'progress'   => $this->progress($child),
            'history'    => $this->history($child),
            'support'    => $this->support($child),
            'badges'     => app(BadgeService::class)->forChild($child),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function childSummary(Child $child): array
    {
        return [
            'id'      => (int) $child->id,
            'name'    => $child->name,
            'avatar'  => $child->avatar,
            'level'   => $child->recommended_level,
            'streak'  => (int) ($child->streak_days ?? 0),
            'stars'   => (int) $child->total_stars,
            'coins'   => (int) $child->star_coins,
            'daily_limit_minutes' => (int) ($child->daily_time_limit_minutes ?? 0),
            'assigned_mission_id' => $child->assigned_mission_id ? (int) $child->assigned_mission_id : null,
        ];
    }

    /**
     * The headline numbers, including the minutes actually played.
     */
    protected function overview(Child $child, Carbon $since, int $days): array
    {
        $today = Carbon::now($this->timezone())->startOfDay();

        $stats = ChildDailyStat::where('child_id', $child->id)
            ->where('day', '>=', $since)
            ->get();

        $todayStat = $stats->firstWhere(fn ($row) => $row->day?->isSameDay($today) ?? false);

        $questions = (int) $stats->sum('questions');
        $correct = (int) $stats->sum('correct');

        return [
            'minutes_today'      => (int) round(((int) ($todayStat->seconds_played ?? 0)) / 60),
            'minutes_in_range'   => (int) round(((int) $stats->sum('seconds_played')) / 60),
            'missions_completed' => (int) $stats->sum('missions_completed'),
            'missions_passed'    => (int) $stats->sum('missions_passed'),
            'questions_answered' => $questions,
            'accuracy_percent'   => $questions > 0 ? (int) round(($correct / $questions) * 100) : null,
            'stars_in_range'     => (int) $stats->sum('stars_earned'),
            'days_played'        => $stats->where('missions_completed', '>', 0)->count(),
            'days_in_range'      => $days,
            // A rhythm is easier to read than a total: which days had any learning.
            'daily' => $stats
                ->sortBy('day')
                ->map(fn (ChildDailyStat $row) => [
                    'day'      => optional($row->day)->toDateString(),
                    'minutes'  => (int) round($row->seconds_played / 60),
                    'missions' => (int) $row->missions_completed,
                    'stars'    => (int) $row->stars_earned,
                ])
                ->values(),
        ];
    }

    /**
     * What the child can do, what is next, and how each subject is going.
     */
    protected function progress(Child $child): array
    {
        $passedMissionIds = ChildProgress::where('child_id', $child->id)
            ->where('status', 'completed')
            ->pluck('mission_id');

        $passed = Mission::whereIn('id', $passedMissionIds)
            ->with('adventureWorld.subject')
            ->get();

        $next = Mission::whereNotIn('id', $passedMissionIds)
            ->whereNotNull('adventure_world_id')
            ->orderBy('sort_order')
            ->limit(3)
            ->get(['id', 'title']);

        return [
            'can_do_now'    => $passed->take(12)->map(fn (Mission $m) => $m->display_title ?? $m->title)->values(),
            'learning_next' => $next->map(fn (Mission $m) => ['id' => (int) $m->id, 'title' => $m->title])->values(),
            'subjects'      => $this->subjectAccuracy($child),
        ];
    }

    /**
     * Accuracy per subject, from the questions actually answered.
     *
     * The website derived this by matching world names against a list of words.
     * This walks the real relationship instead, so a renamed world does not
     * silently move a child's maths score into English.
     */
    protected function subjectAccuracy(Child $child): array
    {
        $attempts = ChildQuestionAttempt::where('child_id', $child->id)
            ->select('mission_id', 'is_correct')
            ->get();

        if ($attempts->isEmpty()) {
            return [];
        }

        $missionSubjects = Mission::whereIn('id', $attempts->pluck('mission_id')->unique())
            ->with('adventureWorld.subject')
            ->get()
            ->mapWithKeys(fn (Mission $m) => [
                $m->id => $m->adventureWorld?->subject,
            ]);

        $buckets = [];

        foreach ($attempts as $attempt) {
            $subject = $missionSubjects[$attempt->mission_id] ?? null;
            $key = $subject?->code ?? $subject?->name ?? 'Other';

            $buckets[$key] ??= ['name' => $subject->name ?? 'Other', 'code' => $subject->code ?? null, 'total' => 0, 'correct' => 0];
            $buckets[$key]['total']++;

            if ($attempt->is_correct) {
                $buckets[$key]['correct']++;
            }
        }

        return collect($buckets)
            ->map(fn (array $bucket) => [
                'name'     => $bucket['name'],
                'code'     => $bucket['code'],
                'answered' => $bucket['total'],
                'accuracy_percent' => $bucket['total'] > 0
                    ? (int) round(($bucket['correct'] / $bucket['total']) * 100)
                    : 0,
            ])
            ->sortByDesc('answered')
            ->values()
            ->all();
    }

    /**
     * Recent attempts, newest first, each with the questions actually missed.
     */
    protected function history(Child $child, int $limit = 12): array
    {
        $attempts = MissionAttempt::where('child_id', $child->id)
            ->with('mission')
            ->orderByDesc('completed_at')
            ->limit($limit)
            ->get();

        if ($attempts->isEmpty()) {
            return [];
        }

        // The mistakes the website's drill-down always showed as empty.
        $wrong = ChildQuestionAttempt::where('child_id', $child->id)
            ->where('is_correct', false)
            ->whereIn('mission_id', $attempts->pluck('mission_id')->unique())
            ->with('question:id,prompt')
            ->orderByDesc('attempted_at')
            ->get()
            ->groupBy('mission_id');

        return $attempts->map(function (MissionAttempt $attempt) use ($wrong) {
            return [
                'mission_id'   => (int) $attempt->mission_id,
                'title'        => $attempt->mission->display_title ?? $attempt->mission->title ?? 'Mission',
                'score'        => (int) $attempt->score,
                'total'        => (int) $attempt->total,
                'percentage'   => $attempt->total > 0 ? (int) round(($attempt->score / $attempt->total) * 100) : 0,
                'stars'        => (int) $attempt->stars,
                'passed'       => (bool) $attempt->passed,
                'minutes'      => (int) round(((int) $attempt->time_spent) / 60),
                'completed_at' => optional($attempt->completed_at)->toIso8601String(),
                'mistakes'     => ($wrong[$attempt->mission_id] ?? collect())
                    ->take(3)
                    ->map(fn ($row) => $row->question->prompt ?? 'A question in this mission')
                    ->values(),
            ];
        })->values()->all();
    }

    /**
     * One thing to work on, and one thing to do about it at home.
     */
    protected function support(Child $child): array
    {
        $latestWrong = ChildQuestionAttempt::where('child_id', $child->id)
            ->where('is_correct', false)
            ->with(['question:id,prompt', 'mission:id,title'])
            ->orderByDesc('attempted_at')
            ->first();

        if (! $latestWrong) {
            return [
                'has_struggle' => false,
                'headline'     => "{$child->name} has not got stuck on anything yet.",
                'activity'     => 'Keep the streak going — a short mission a day is plenty at this age.',
            ];
        }

        return [
            'has_struggle' => true,
            'headline'     => trim(($latestWrong->mission->title ?? 'A mission') . ': ' . ($latestWrong->question->prompt ?? 'a question')),
            'activity'     => "Try it away from the screen with {$child->name}: use real objects they can touch and count out loud together.",
            'mission_id'   => $latestWrong->mission_id ? (int) $latestWrong->mission_id : null,
        ];
    }

    protected function timezone(): string
    {
        return (string) config('kiddoquest.timezone', 'Africa/Nairobi');
    }
}
