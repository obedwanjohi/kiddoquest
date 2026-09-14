<?php

namespace App\Services\Learning;

use App\Models\ChildQuestionAttempt;
use App\Models\Guardian;
use App\Models\Mission;
use App\Models\MissionAttempt;
use App\Models\Subject;

/**
 * Everything the parent dashboard shows.
 *
 * Moved here unchanged from ParentDashboardController so that the website and
 * the app render the same report from the same code: the same competencies,
 * the same heat map, the same growth label, the same struggle area. A parent
 * who checks both should never see two different accounts of their child.
 *
 * One thing is not unchanged. Each mission's `mistakes` list was hard-coded
 * empty, so the drilldown always said "Perfect execution!" whatever happened;
 * it now lists the questions the child actually got wrong.
 */
class ParentDashboardService
{
    /**
     * @return array{children: \Illuminate\Support\Collection, reports: array, allMissions: \Illuminate\Support\Collection, selectedChildId: int|null, selectedChild: mixed}
     */
    public function build(Guardian $guardian, ?int $requestedChildId): array
    {
        $children = $guardian->children()->orderBy('created_at')->get();
        $childIds = $children->pluck('id');

        $allMissions = Mission::where('status', 'published')->get();
        if ($allMissions->isEmpty()) {
            $allMissions = Mission::all();
        }

        // Child Selection Dropdown Logic — only this guardian's children are selectable
        $selectedChildId = (int) $requestedChildId;
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        if (! $selectedChild) {
            $playedChildId = MissionAttempt::whereIn('child_id', $childIds)->latest('completed_at')->value('child_id');
            $selectedChild = $children->firstWhere('id', $playedChildId) ?? $children->first();
            $selectedChildId = $selectedChild ? $selectedChild->id : null;
        }

        $allSubjects = Subject::with('adventureWorlds.missions')->get();
        $subjectMissionsMap = [];
        foreach ($allSubjects as $subj) {
            $mIds = [];
            foreach ($subj->adventureWorlds as $w) {
                foreach ($w->missions as $m) {
                    $mIds[] = $m->id;
                }
            }
            $subjectMissionsMap[$subj->id] = $mIds;
        }

        $reports = [];

        foreach ($children as $child) {
            $reports[$child->id] = $this->reportFor($child, $allSubjects, $subjectMissionsMap);
        }

        return compact('children', 'reports', 'allMissions', 'selectedChildId', 'selectedChild');
    }

    protected function reportFor($child, $allSubjects, array $subjectMissionsMap): array
    {
        $totalMissions = 0;
        $passedMissions = 0;
        $totalQuestions = 0;
        $correctQuestions = 0;
        $accuracyRate = 0;
        $missionHistory = [];
        $attempts = collect();

        try {
            $attempts = MissionAttempt::where('child_id', $child->id)
                ->with('mission')
                ->orderByDesc('completed_at')
                ->get();

            $questionAttempts = ChildQuestionAttempt::where('child_id', $child->id)->get();

            // The questions this child got wrong, per mission, for the drilldown.
            $wrongByMission = ChildQuestionAttempt::where('child_id', $child->id)
                ->where('is_correct', false)
                ->with('question:id,prompt')
                ->orderByDesc('attempted_at')
                ->get()
                ->groupBy('mission_id');

            if ($attempts->isNotEmpty()) {
                $totalMissions = $attempts->pluck('mission_id')->unique()->count();
                $passedMissions = $attempts->where('passed', true)->pluck('mission_id')->unique()->count();

                // Build real mission history from database attempts
                $groupedAttempts = $attempts->groupBy('mission_id');
                foreach ($groupedAttempts as $missionId => $mAttempts) {
                    $mObj = $mAttempts->first()->mission;
                    $title = $mObj ? ($mObj->title ?? $mObj->display_title ?? $mObj->name) : "Mission #{$missionId}";

                    $attemptList = [];
                    foreach ($mAttempts->take(5) as $idx => $att) {
                        $attemptList[] = [
                            'attempt' => $mAttempts->count() - $idx,
                            'score'   => $att->percentage() . '%',
                            'date'    => $att->completed_at ? $att->completed_at->diffForHumans() : 'Recently',
                        ];
                    }

                    $missionHistory[] = [
                        'mission_title'  => $title,
                        'attempts_count' => $mAttempts->count(),
                        'best_stars'     => $mAttempts->max('stars'),
                        'last_played'    => $mAttempts->first()->completed_at ? $mAttempts->first()->completed_at->diffForHumans() : 'Recently',
                        'attempts'       => array_reverse($attemptList),
                        'mistakes'       => ($wrongByMission[$missionId] ?? collect())
                            ->map(fn ($row) => $row->question->prompt ?? null)
                            ->filter()
                            ->unique()
                            ->take(5)
                            ->values()
                            ->all(),
                    ];
                }
            }

            if ($questionAttempts->isNotEmpty()) {
                $totalQuestions = $questionAttempts->count();
                $correctQuestions = $questionAttempts->where('is_correct', true)->count();
                $accuracyRate = $totalQuestions > 0 ? (int) round(($correctQuestions / $totalQuestions) * 100) : 0;
            } elseif ($attempts->isNotEmpty()) {
                // Fallback to overall score from mission_attempts if question_attempts is empty
                $sumScore = $attempts->sum('score');
                $sumTotal = $attempts->sum('total');
                $accuracyRate = $sumTotal > 0 ? (int) round(($sumScore / $sumTotal) * 100) : 0;
                $totalQuestions = $sumTotal;
            }
        } catch (\Throwable $e) {
            // Keep clean empty fallbacks
        }

        // 📈 Dynamic Real Database Analytics for Competencies & Heat Map
        $realCanDo = [];
        $realLearningNext = [];
        $realHeatMap = [];

        // Get all completed missions for this child
        $completedMissionIds = $attempts->where('passed', true)->pluck('mission_id')->unique();
        $completedMissionsList = Mission::whereIn('id', $completedMissionIds)->get();

        foreach ($completedMissionsList as $cMiss) {
            $realCanDo[] = "Mastered {$cMiss->title}";
        }

        // Find next upcoming missions not completed yet
        $nextMissions = Mission::whereNotIn('id', $completedMissionIds)->take(3)->get();
        foreach ($nextMissions as $nMiss) {
            $realLearningNext[] = $nMiss->title;
        }

        // Calculate Real Heat Map per Subject from pre-fetched in-memory maps
        foreach ($allSubjects as $subj) {
            $subjMissions = $subjectMissionsMap[$subj->id] ?? [];

            if (empty($subjMissions)) {
                continue;
            }

            $subjAttempts = $attempts->whereIn('mission_id', $subjMissions);
            if ($subjAttempts->isNotEmpty()) {
                $sumScore = $subjAttempts->sum('score');
                $sumTotal = $subjAttempts->sum('total');
                $avgScore = $sumTotal > 0 ? (int) round(($sumScore / $sumTotal) * 100) : 0;
            } else {
                $avgScore = 0;
            }

            $filledBars = (int) round(($avgScore / 100) * 8);

            $realHeatMap[] = [
                'name'  => $subj->name,
                'score' => $avgScore,
                'bar'   => max(0, min(8, $filledBars)),
                'total' => 8,
            ];
        }

        if (empty($realHeatMap)) {
            $realHeatMap = [
                ['name' => 'Mathematics Activities', 'score' => $accuracyRate, 'bar' => max(1, (int) round(($accuracyRate / 100) * 8)), 'total' => 8],
                ['name' => 'Language & Phonics', 'score' => 0, 'bar' => 0, 'total' => 8],
                ['name' => 'CRE & Moral Values', 'score' => 0, 'bar' => 0, 'total' => 8],
            ];
        }

        // Calculate Real Struggle Area & Recommended Home Activity from Database
        $wrongAttempt = ChildQuestionAttempt::where('child_id', $child->id)
            ->where('is_correct', false)
            ->with(['question', 'mission'])
            ->latest('attempted_at')
            ->first();

        if ($wrongAttempt) {
            $qText = $wrongAttempt->question->prompt ?? 'quiz question';
            $mTitle = $wrongAttempt->mission->title ?? 'quiz';
            $realMistake = "Struggled on {$mTitle}: \"{$qText}\"";
            $realActivity = "Practice counting 3 physical objects (like spoons or toys) with {$child->name} at home while touching each object!";
            $hasStruggle = true;
        } elseif ($attempts->isNotEmpty() && $attempts->first()->passed) {
            $mTitle = $attempts->first()->mission->title ?? 'Mission';
            $realMistake = "No struggle areas identified! {$child->name} scored {$attempts->first()->percentage()}% on {$mTitle}!";
            $realActivity = "Keep up the great momentum! Assign tomorrow's focus mission to continue {$child->name}'s learning adventure.";
            $hasStruggle = false;
        } else {
            $realMistake = "No struggle areas recorded yet for {$child->name}.";
            $realActivity = "Start Mission 1 on the Adventure Map to track {$child->name}'s learning progress!";
            $hasStruggle = false;
        }

        $activeData = [
            'can_do'        => $realCanDo,
            'learning_next' => $realLearningNext,
            'heat_map'      => $realHeatMap,
            'roadmap'       => [
                'completed' => $completedMissionsList->pluck('title')->take(3)->toArray() ?: ['Numbers 1–3'],
                'current'   => $nextMissions->first()->title ?? 'Counting Quantities',
                'next'      => 'Addition within 5 & Sight Words',
                'future'    => ['Kenyan Currency Coins (KES)', 'Reading Short Sentences'],
            ],
            'mistake'      => $realMistake,
            'activity'     => $realActivity,
            'has_struggle' => $hasStruggle,
        ];

        // Calculate Real Growth % from actual attempt scores
        $growthLabel = '🌱 Ready to Start';
        $growthPercent = 0;

        if ($attempts->count() >= 2) {
            $oldestPct = $attempts->last()->percentage();
            $latestPct = $attempts->first()->percentage();
            $diff = $latestPct - $oldestPct;

            if ($diff > 0) {
                $growthLabel = "📈 +{$diff}% Growth";
            } elseif ($diff == 0) {
                $growthLabel = "⭐ Consistent ({$latestPct}%)";
            } else {
                $growthLabel = "🔄 Practice Mode ({$latestPct}%)";
            }
            $growthPercent = $diff;
        } elseif ($attempts->count() === 1) {
            $latestPct = $attempts->first()->percentage();
            $growthLabel = "🌟 First Mission ({$latestPct}%)";
        }

        // Real learning time today / this week from completed missions (seconds -> minutes)
        $todaySeconds = (int) $attempts->filter(fn ($a) => $a->completed_at && $a->completed_at->isToday())->sum('time_spent');
        $weekSeconds = (int) $attempts->filter(fn ($a) => $a->completed_at && $a->completed_at->gte(now()->subDays(7)))->sum('time_spent');

        return [
            'total_missions'      => $totalMissions,
            'passed_missions'     => $passedMissions,
            'total_questions'     => $totalQuestions,
            'accuracy_rate'       => $accuracyRate,
            'learning_time_today' => (int) round($todaySeconds / 60) . ' mins',
            'learning_time_week'  => (int) round($weekSeconds / 60) . ' mins',
            'streak_days'         => $child->streak_days ?? 1,
            'can_do_now'          => $activeData['can_do'],
            'learning_next'       => $activeData['learning_next'],
            'skills_heat_map'     => $activeData['heat_map'],
            'roadmap'             => $activeData['roadmap'],
            'growth'              => ['growth_label' => $growthLabel, 'growth_percent' => $growthPercent],
            'mistake_action'      => ['mistake' => $activeData['mistake'], 'activity' => $activeData['activity'], 'has_struggle' => $activeData['has_struggle']],
            'mission_history'     => $missionHistory,
            'assigned_mission'    => $child->assigned_mission_id ? Mission::find($child->assigned_mission_id) : null,
        ];
    }
}
