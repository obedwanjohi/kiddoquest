<?php

namespace App\Http\Controllers\Kid;

use App\Http\Controllers\Controller;
use App\Models\AdventureWorld;
use App\Models\Child;
use App\Models\ChildProgress;
use App\Models\QuestionBank;
use App\Models\Mission;
use App\Models\MissionAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KidMissionController extends Controller
{
    /**
     * Show the quiz game engine.
     */
    public function show(AdventureWorld $world, Mission $mission)
    {
        $child = $this->activeChild();

        if (($child->daily_time_limit_minutes ?? 0) > 0 && $child->remaining_time_minutes <= 0) {
            return redirect()->route('kids.songs')->with('info', '🌟 Quiz time for today is complete! Enjoy your Music & Songs Hub!');
        }

        $mission->load([
            'questionBank.questions.options', 'questionBank.questions.quizType',
            'questionBank.assignedQuestions.options', 'questionBank.assignedQuestions.quizType'
        ]);

        $drawnQuestions = collect();
        $isRetest = false;

        if ($mission->questionBank && $mission->questionBank->pool_count > 0) {
            // Exclusion Filter: Fetch question IDs attempted by this child for this mission in the last 7 days
            $recentlyAttemptedIds = $child->getRecentlyAttemptedQuestionIds($mission->id, 7);
            $weakQuestionIds = $child->getWeakQuestionIds($mission->questionBank->id);
            $isRetest = !empty($recentlyAttemptedIds);

            $drawnQuestions = $mission->questionBank->drawQuestions(
                $mission->questions_per_session ?? 10,
                true,
                $recentlyAttemptedIds,
                $weakQuestionIds
            );
        }

        // We bind the drawn questions to the mission object dynamically so the view can use them
        $mission->setRelation('questions', $drawnQuestions);

        return view('kids.mission.engine', compact('child', 'mission', 'world', 'isRetest'));
    }

    /**
     * Submit quiz answers — saves attempt, updates progress, awards stars.
     */
    public function submit(Request $request, AdventureWorld $world, Mission $mission)
    {
        $validated = $request->validate([
            'score'      => 'required|integer|min:0',
            'total'      => 'required|integer|min:1',
            'stars'      => 'required|integer|min:0|max:3',
            'answers'    => 'nullable',
            'time_spent' => 'nullable|integer|min:0',
        ]);

        $child = $this->activeChild();

        // Decode JSON answers string if sent as string
        if (! empty($validated['answers']) && is_string($validated['answers'])) {
            $decoded = json_decode($validated['answers'], true);
            $validated['answers'] = is_array($decoded) ? $decoded : [];
        }

        try {
            DB::transaction(function () use ($validated, $child, $mission) {
                $score      = (int) $validated['score'];
                $total      = (int) $validated['total'];
                $stars      = (int) $validated['stars'];
                $percentage = $total > 0 ? (int) round(($score / $total) * 100) : 0;
                $passed     = $percentage >= ($mission->pass_threshold_percent ?? 60);

                // Get previous best BEFORE creating the new attempt
                $previousBest = MissionAttempt::where('child_id', $child->id)
                    ->where('mission_id', $mission->id)
                    ->max('stars') ?? 0;

                // 1. Save the mission attempt (permanent record)
                MissionAttempt::create([
                    'child_id'     => $child->id,
                    'mission_id'   => $mission->id,
                    'score'        => $score,
                    'total'        => $total,
                    'stars'        => $stars,
                    'passed'       => $passed,
                    'answers'      => $validated['answers'] ?? [],
                    'time_spent'   => $validated['time_spent'] ?? 0,
                    'completed_at' => now(),
                ]);

                // 1b. Record individual question attempts
                if (is_array($validated['answers'])) {
                    foreach ($validated['answers'] as $qAns) {
                        if (is_array($qAns) && isset($qAns['question_id']) && \App\Models\QuizQuestion::where('id', $qAns['question_id'])->exists()) {
                            try {
                                \App\Models\ChildQuestionAttempt::create([
                                    'child_id'           => $child->id,
                                    'mission_id'         => $mission->id,
                                    'question_bank_id'   => $mission->questionBank->id ?? null,
                                    'question_id'        => (int) $qAns['question_id'],
                                    'is_correct'         => (bool) ($qAns['correct'] ?? $qAns['is_correct'] ?? false),
                                    'time_spent_seconds' => (int) ($qAns['time_spent'] ?? 0),
                                    'attempted_at'       => now(),
                                ]);
                            } catch (\Throwable $attemptErr) {
                                Log::warning('Failed to log question attempt', ['error' => $attemptErr->getMessage()]);
                            }
                        }
                    }
                }

                // 2. Update child progress for this mission
                $progress = ChildProgress::firstOrNew([
                    'child_id'   => $child->id,
                    'mission_id' => $mission->id,
                ]);

                if ($stars > ($progress->stars_earned ?? 0)) {
                    $progress->stars_earned = $stars;
                }

                if ($passed) {
                    $progress->status       = 'completed';
                    $progress->completed_at = now();
                } elseif ($progress->status !== 'completed') {
                    $progress->status = 'in_progress';
                }

                if (! $progress->started_at) {
                    $progress->started_at = now();
                }

                $progress->save();

                // 3. Award net-new stars to child's total
                $netNewStars = max(0, $stars - $previousBest);

                // 4. Calculate & Award Star Coins + Streak Bonus
                $baseCoins = 10;
                $performanceCoins = match ($stars) {
                    3 => 15,
                    2 => 5,
                    default => 0,
                };

                $today = now()->toDateString();
                $lastStreakDate = $child->last_streak_date ? $child->last_streak_date->toDateString() : null;
                $streakBonusCoins = 0;

                if (! $lastStreakDate) {
                    $child->streak_days = 1;
                    $child->last_streak_date = now();
                    $streakBonusCoins = 5;
                } elseif ($lastStreakDate === now()->subDay()->toDateString()) {
                    $child->streak_days = ($child->streak_days ?? 1) + 1;
                    $child->last_streak_date = now();
                    $streakBonusCoins = 10;
                } elseif ($lastStreakDate !== $today) {
                    $child->streak_days = 1;
                    $child->last_streak_date = now();
                }

                $earnedCoins = $baseCoins + $performanceCoins + $streakBonusCoins;
                $child->last_played_at = now();
                $child->save();

                // Increment total_stars and star_coins atomically in database
                if ($netNewStars > 0) {
                    $child->increment('total_stars', $netNewStars);
                }
                if ($earnedCoins > 0) {
                    $child->increment('star_coins', $earnedCoins);
                }

                $child->refresh();

                $validated['earned_coins'] = $earnedCoins;
            });

            $score = (int) $validated['score'];
            $total = (int) $validated['total'];
            $stars = (int) $validated['stars'];
            $earnedCoins = (int) ($validated['earned_coins'] ?? 10);

            // Store in session for celebration view fallback
            session([
                'celebration' => [
                    'stars'        => $stars,
                    'score'        => $score,
                    'total'        => $total,
                    'earned_coins' => $earnedCoins,
                    'streak_days'  => $child->streak_days ?? 1,
                    'mission_id'   => $mission->id,
                    'world_id'     => $world->id,
                ]
            ]);

            if ($request->wantsJson() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success'      => true,
                    'score'        => $score,
                    'total'        => $total,
                    'stars'        => $stars,
                    'earned_coins' => $earnedCoins,
                    'total_stars'  => $child->total_stars,
                    'star_coins'   => $child->star_coins,
                    'redirect_url' => route('kids.celebration')
                ], 200);
            }

            return redirect()
                ->route('kids.celebration')
                ->with('success', 'Quiz complete!');

        } catch (\Exception $e) {
            Log::error('Mission submission failed: ' . $e->getMessage(), [
                'child_id'   => $child->id ?? null,
                'mission_id' => $mission->id ?? null,
                'error'      => $e->getMessage(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
                'trace'      => $e->getTraceAsString(),
            ]);

            if ($request->wantsJson() || $request->isJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'error'   => $e->getMessage(),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ], 500);
            }

            return redirect()
                ->route('kids.celebration')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get the active child from session.
     */
    protected function activeChild(): Child
    {
        $childId = session('active_child_id');

        if (! $childId) {
            abort(redirect()->route('kids.profiles'));
        }

        $child = Child::find($childId);

        if (! $child) {
            abort(redirect()->route('kids.profiles'));
        }

        $guardian = Auth::guard('guardian')->user();
        if ($guardian && $child->guardian_id !== $guardian->id) {
            abort(redirect()->route('kids.profiles'));
        }

        return $child;
    }
}