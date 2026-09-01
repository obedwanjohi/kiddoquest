<?php

namespace App\Http\Controllers\Kid;

use App\Http\Controllers\Controller;
use App\Models\AdventureWorld;
use App\Models\Child;
use App\Models\Guardian;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KidController extends Controller
{
    /**
     * Profile picker — "Who's Playing?"
     * Shows all children under the logged-in guardian.
     */
    public function profiles(): View
    {
        $guardian = Auth::guard('guardian')->user() ?? Guardian::first();
        $children = $guardian ? Child::where('guardian_id', $guardian->id)->get() : Child::all();

        if ($children->isEmpty()) {
            $children = Child::all();
        }

        return view('kids.profiles', compact('children'));
    }

    /**
     * Enter as a specific child (sets the child session).
     */
    public function enterChild(Request $request, Child $child): \Illuminate\Http\RedirectResponse
    {
        $guardian = Auth::guard('guardian')->user();

        // If a guardian session is active, verify ownership
        if ($guardian && $child->guardian_id !== $guardian->id) {
            abort(403, 'Unauthorized child profile access.');
        }

        // Auto-login the child's guardian if accessing in kid mode
        if (!$guardian && $child->guardian) {
            Auth::guard('guardian')->login($child->guardian);
        }

        session(['active_child_id' => $child->id]);

        return redirect()->route('kids.map');
    }

    /**
     * Adventure map home — the main hub for the child.
     */
    public function map(): View
    {
        $child = $this->activeChild();
        $rawLevel = strtolower(str_replace(['_', '-'], ' ', $child->recommended_level ?? ''));

        $worldsQuery = AdventureWorld::with([
            'subject.level',
            'missions' => function ($q) {
                $q->where('status', 'published')->orderBy('sort_order');
            }
        ])->orderBy('sort_order');

        if ($rawLevel) {
            $worlds = $worldsQuery->where(function ($query) use ($rawLevel) {
                $query->whereHas('subject.level', function ($q) use ($rawLevel) {
                    if (str_contains($rawLevel, 'play') || str_contains($rawLevel, 'pg')) {
                        $q->where('code', 'PG')
                          ->orWhere('name', 'like', '%play%');
                    } elseif (str_contains($rawLevel, 'pp1')) {
                        $q->where('code', 'PP1')
                          ->orWhere('name', 'like', '%pp1%');
                    } elseif (str_contains($rawLevel, 'pp2')) {
                        $q->where('code', 'PP2')
                          ->orWhere('name', 'like', '%pp2%');
                    } else {
                        $q->where('code', strtoupper($rawLevel))
                          ->orWhere('name', 'like', "%{$rawLevel}%");
                    }
                });

                // Always unlock Tracing Worlds & Speak Repeat Safari for all levels (Playgroup, PP1, PP2)
                $query->orWhereIn('slug', [
                    'line-tracing-trail', 'letter-tracing-safari', 'number-tracing-kingdom', 'speak-repeat-safari'
                ]);

                // Match Playgroup world slugs directly for Play Group profiles
                if (str_contains($rawLevel, 'play') || str_contains($rawLevel, 'pg')) {
                    $query->orWhereIn('slug', [
                        'whispering-forest', 'sunny-meadow', 'cookie-trail',
                        'safari-plains', 'castle-of-discovery',
                        'ocean-cove', 'ocean-cove-creation', 'kindness-village', 'rainbow-mountain', 'rainbow-mountain-values',
                        'creation-realm', 'jesus-realm', 'christian-values-realm',
                        'speak-repeat-safari'
                    ]);
                }
            })->get();
        } else {
            $worlds = $worldsQuery->get();
        }

        // Pre-fetch all progress for active child in 1 fast query to eliminate N+1 DB roundtrips
        $progressRecords = $child->progress()->get();
        $progressMap = $progressRecords->pluck('status', 'mission_id')->toArray();
        $starsMap = $progressRecords->pluck('stars_earned', 'mission_id')->toArray();

        return view('kids.map', compact('child', 'worlds', 'progressMap', 'starsMap'));
    }

    /**
     * A themed world view.
     */
    public function world(AdventureWorld $world, \Illuminate\Http\Request $request): View
    {
        $child = $this->activeChild();
        $query = $world->missions()->orderBy('sort_order');

        if ($tier = $request->query('tier')) {
            if ($tier === 'easy') {
                $query->where('title', 'like', '%Easy%');
            } elseif ($tier === 'medium') {
                $query->where('title', 'like', '%Medium%');
            } elseif ($tier === 'hard') {
                $query->where('title', 'like', '%Hard%');
            }
        }

        $missions = $query->get();

        return view('kids.world', compact('child', 'world', 'missions'));
    }

    /**
     * Mission Intro — story transition before the lesson/quiz.
     * Leo sets the scene to make learning feel like an adventure.
     */
    public function missionIntro(AdventureWorld $world, Mission $mission): View
    {
        $child = $this->activeChild();

        // The story title is now part of the mission directly
        $storyTitle = $mission->display_title;

        return view('kids.mission-intro', compact('child', 'world', 'mission', 'storyTitle'));
    }

    /**
     * Video — the teaching page (content before the challenge).
     */
    public function video(AdventureWorld $world, Mission $mission): View
    {
        $child = $this->activeChild();

        return view('kids.mission-video', compact('child', 'world', 'mission'));
    }

    /**
     * Exit kid mode (back to guardian dashboard).
     */
    public function exit(Request $request): \Illuminate\Http\RedirectResponse
    {
        session()->forget('active_child_id');

        return redirect()->route('guardian.dashboard');
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