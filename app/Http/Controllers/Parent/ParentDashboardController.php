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

        // The report is built by ParentDashboardService, which the app's
        // dashboard endpoint uses too, so both show a parent the same child.
        [
            'children'        => $children,
            'reports'         => $reports,
            'allMissions'     => $allMissions,
            'selectedChildId' => $selectedChildId,
            'selectedChild'   => $selectedChild,
        ] = app(\App\Services\Learning\ParentDashboardService::class)->build(
            $guardian,
            (int) ($request->query('child_id') ?? $request->query('child'))
        );

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
