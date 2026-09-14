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
        // guardian.auth guarantees a signed-in parent; only their own children are listed.
        $guardian = Auth::guard('guardian')->user();
        $children = $guardian->children()->orderBy('created_at')->get();

        return view('kids.profiles', compact('children'));
    }

    /**
     * Enter as a specific child (sets the child session).
     */
    public function enterChild(Request $request, Child $child): \Illuminate\Http\RedirectResponse
    {
        $guardian = Auth::guard('guardian')->user();

        // Only the signed-in parent's own children can be entered. (This route used to
        // log in the child's guardian for anyone who knew a child id.)
        if (! $guardian || $child->guardian_id !== $guardian->id) {
            abort(403, 'Unauthorized child profile access.');
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

        // The rule for which worlds a child sees lives in KidMapService, which
        // the app's map endpoint uses too, so the two can never disagree.
        $maps = app(\App\Services\Learning\KidMapService::class);

        $worlds = $maps->worldsFor($child);
        $progress = $maps->progressFor($child);
        $progressMap = $progress['status'];
        $starsMap = $progress['stars'];

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
        if (! $guardian || $child->guardian_id !== $guardian->id) {
            session()->forget('active_child_id');
            abort(redirect()->route('kids.profiles'));
        }

        return $child;
    }
}