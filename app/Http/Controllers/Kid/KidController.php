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
        $levelCode = $child->recommended_level;

        $worldsQuery = AdventureWorld::with('subject.level')->orderBy('sort_order');

        if ($levelCode) {
            $filteredWorlds = (clone $worldsQuery)->whereHas('subject.level', function($q) use ($levelCode) {
                $q->where('code', $levelCode)
                  ->orWhere('name', 'like', "%{$levelCode}%");
            })->get();

            $worlds = $filteredWorlds->isNotEmpty() ? $filteredWorlds : $worldsQuery->get();
        } else {
            $worlds = $worldsQuery->get();
        }

        return view('kids.map', compact('child', 'worlds'));
    }

    /**
     * A themed world view.
     */
    public function world(AdventureWorld $world): View
    {
        $child = $this->activeChild();
        $missions = $world->missions()->orderBy('sort_order')->get();

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