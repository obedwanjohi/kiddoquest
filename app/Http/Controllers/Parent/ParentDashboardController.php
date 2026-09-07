<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildQuestionAttempt;
use App\Models\Guardian;
use App\Models\Mission;
use App\Models\MissionAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class ParentDashboardController extends Controller
{
    /** Wrong-PIN attempts allowed per guardian before a lockout. */
    private const PIN_MAX_ATTEMPTS = 5;

    /** Lockout length in seconds once the limit is hit. */
    private const PIN_LOCKOUT_SECONDS = 60;

    /**
     * The signed-in guardian. Routes sit behind guardian.auth, so this never
     * falls back to another family's account (it used to use Guardian::first()).
     */
    protected function guardian(): Guardian
    {
        /** @var Guardian|null $guardian */
        $guardian = Auth::guard('guardian')->user();
        abort_unless($guardian, 403, 'Please sign in as a parent.');

        return $guardian;
    }

    /**
     * Display interactive 4-digit PIN pad modal.
     */
    public function showPinGate(): View
    {
        $guardian = $this->guardian();
        $showDefaultPinHint = ! $guardian->hasCustomPin();

        return view('parent.pin-gate', compact('guardian', 'showDefaultPinHint'));
    }

    /**
     * Verify entered 4-digit PIN (hashed comparison, 5 tries then a 60 s lockout).
     */
    public function verifyPin(Request $request): RedirectResponse
    {
        $request->validate([
            'pin' => 'required|string|size:4',
        ]);

        $guardian = $this->guardian();
        $key = 'parent-pin:' . $guardian->id;

        if (RateLimiter::tooManyAttempts($key, self::PIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->with('error', "Too many tries. Please wait {$seconds} seconds and try again.");
        }

        if ($guardian->verifyPin(trim((string) $request->input('pin')))) {
            RateLimiter::clear($key);
            session(['parent_unlocked' => true, 'parent_unlocked_at' => now()->toIso8601String()]);

            return redirect()->route('parent.dashboard')->with('success', '🔓 Welcome to Parent Zone!');
        }

        RateLimiter::hit($key, self::PIN_LOCKOUT_SECONDS);
        $left = max(0, self::PIN_MAX_ATTEMPTS - RateLimiter::attempts($key));

        return back()->with('error', $left > 0
            ? "Incorrect PIN. {$left} " . ($left === 1 ? 'try' : 'tries') . ' left.'
            : 'Incorrect PIN. Please wait a minute before trying again.');
    }

    /**
     * Main Parent Dashboard view (Clean 4-Tab Architecture + Ask AI Coach).
     */
    public function index(Request $request)
    {
        if (! session('parent_unlocked')) {
            return redirect()->route('parent.pin_gate');
        }

        $timeframe = $request->query('timeframe', '7days');
        $selectedSubject = $request->query('subject', 'all');

        $guardian = $this->guardian();
        $children = $guardian->children()->orderBy('created_at')->get();
        $childIds = $children->pluck('id');

        $allMissions = Mission::where('status', 'published')->get();
        if ($allMissions->isEmpty()) {
            $allMissions = Mission::all();
        }

        // Child Selection Dropdown Logic — only this guardian's children are selectable
        $selectedChildId = (int) ($request->query('child_id') ?? $request->query('child'));
        $selectedChild = $children->firstWhere('id', $selectedChildId);

        if (! $selectedChild) {
            $playedChildId = MissionAttempt::whereIn('child_id', $childIds)->latest('completed_at')->value('child_id');
            $selectedChild = $children->firstWhere('id', $playedChildId) ?? $children->first();
            $selectedChildId = $selectedChild ? $selectedChild->id : null;
        }

        $allSubjects = \App\Models\Subject::with('adventureWorlds.missions')->get();
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
                            'mistakes'       => [],
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

            $reports[$child->id] = [
                'total_missions'     => $totalMissions,
                'passed_missions'    => $passedMissions,
                'total_questions'    => $totalQuestions,
                'accuracy_rate'      => $accuracyRate,
                'learning_time_today'=> (int) round($todaySeconds / 60) . ' mins',
                'learning_time_week' => (int) round($weekSeconds / 60) . ' mins',
                'streak_days'        => $child->streak_days ?? 1,
                'can_do_now'         => $activeData['can_do'],
                'learning_next'      => $activeData['learning_next'],
                'skills_heat_map'    => $activeData['heat_map'],
                'roadmap'            => $activeData['roadmap'],
                'growth'             => ['growth_label' => $growthLabel, 'growth_percent' => $growthPercent],
                'mistake_action'     => ['mistake' => $activeData['mistake'], 'activity' => $activeData['activity'], 'has_struggle' => $activeData['has_struggle']],
                'mission_history'    => $missionHistory,
                'assigned_mission'   => $child->assigned_mission_id ? Mission::find($child->assigned_mission_id) : null,
            ];
        }

        return view('parent.dashboard', compact('guardian', 'children', 'reports', 'timeframe', 'selectedSubject', 'allMissions', 'selectedChildId', 'selectedChild'));
    }

    /**
     * Ask AI Pedagogy Assistant endpoint (with guardrails & free LLM integration).
     */
    public function askAi(Request $request): JsonResponse
    {
        $question = trim((string) $request->input('question', ''));
        $childId = $request->input('child_id');

        $guardian = $this->guardian();
        $child = $childId
            ? $guardian->children()->find($childId)
            : $guardian->children()->first();

        if (! $child) {
            return response()->json([
                'success' => false,
                'answer'  => 'Add a child profile first so the coach can personalise its advice.',
            ]);
        }

        // Calculate performance summary for AI context
        $attempts = MissionAttempt::where('child_id', $child->id)->get();
        $passedMissions = $attempts->where('passed', true)->pluck('mission_id')->unique()->count();
        $totalMissions = $attempts->pluck('mission_id')->unique()->count();
        $sumScore = $attempts->sum('score');
        $sumTotal = $attempts->sum('total');
        $accuracyRate = $sumTotal > 0 ? (int) round(($sumScore / $sumTotal) * 100) : 80;

        $perf = [
            'accuracy_rate'   => $accuracyRate,
            'passed_missions' => $passedMissions,
            'total_missions'  => $totalMissions,
        ];

        $aiService = app(\App\Services\ParentAiService::class);
        $answer = $aiService->generateAdvice($child, $perf, $question);

        return response()->json([
            'success' => true,
            'answer'  => $answer,
        ]);
    }

    /**
     * Parent assigns a Focus Mission (only for their own child).
     */
    public function assignFocusMission(Request $request): RedirectResponse
    {
        $request->validate([
            'child_id'   => 'required|integer',
            'mission_id' => 'nullable|exists:missions,id',
        ]);

        $child = $this->guardian()->children()->findOrFail($request->input('child_id'));
        $child->assigned_mission_id = $request->input('mission_id');
        $child->save();

        if ($child->assigned_mission_id) {
            $mission = Mission::find($child->assigned_mission_id);

            return back()->with('success', "📌 Assigned '{$mission->title}' as focus mission for {$child->name}!");
        }

        return back()->with('success', 'Focus mission assignment cleared.');
    }

    /**
     * Update Parent PIN (stored hashed).
     */
    public function updatePin(Request $request): RedirectResponse
    {
        $request->validate([
            'new_pin' => 'required|string|size:4|regex:/^[0-9]{4}$/',
        ]);

        $guardian = $this->guardian();
        $guardian->parent_pin = $request->input('new_pin'); // hashed by the model cast
        $guardian->pin_changed_at = now();
        $guardian->save();

        return back()->with('success', '🔐 Parent PIN updated successfully!');
    }

    /**
     * Update Child Daily Screen Time Limit (only for their own child).
     */
    public function updateScreenTime(Request $request): RedirectResponse
    {
        $request->validate([
            'child_id'                 => 'required|integer',
            'daily_time_limit_minutes' => 'required|integer|min:0|max:300',
        ]);

        $child = $this->guardian()->children()->findOrFail($request->input('child_id'));
        $child->daily_time_limit_minutes = (int) $request->input('daily_time_limit_minutes');
        $child->save();

        return back()->with('success', "⏰ Screen time limit updated for {$child->name}!");
    }

    /**
     * Update Devotional & Songs Hub Settings.
     * (The columns are created by a proper migration now — no runtime schema changes.)
     */
    public function updateDevotionalSettings(Request $request): RedirectResponse
    {
        $guardian = $this->guardian();
        $guardian->enable_devotional = $request->boolean('enable_devotional');
        $guardian->enable_songs_hub = $request->boolean('enable_songs_hub');
        $guardian->save();

        session([
            'enable_devotional' => $guardian->enable_devotional,
            'enable_songs_hub'  => $guardian->enable_songs_hub,
        ]);

        return back()->with('success', '✨ Devotional & Feature controls updated successfully!');
    }

    /**
     * Lock Parent Zone.
     */
    public function lockSession(): RedirectResponse
    {
        session()->forget(['parent_unlocked', 'parent_unlocked_at']);

        return redirect()->route('kids.profiles')->with('info', 'Parent Zone locked.');
    }
}
