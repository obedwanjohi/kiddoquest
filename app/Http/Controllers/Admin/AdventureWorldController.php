<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Mission;
use Illuminate\Http\Request;

use App\Models\Subject;

class AdventureWorldController extends Controller
{
    public function index()
    {
        $worlds = AdventureWorld::with(['subject'])->withCount('missions')->orderBy('sort_order')->get();
        return view('admin.adventure-worlds.index', compact('worlds'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.adventure-worlds.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:adventure_worlds,slug',
            'subject_id' => 'nullable|exists:subjects,id',
            'theme_color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_locked' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_locked'] = $request->boolean('is_locked');
        $validated['sort_order'] = $validated['sort_order'] ?? AdventureWorld::count();

        AdventureWorld::create($validated);

        return redirect()->route('admin.adventure-worlds.index')
            ->with('success', 'Adventure World created successfully!');
    }

    public function edit(AdventureWorld $adventureWorld)
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.adventure-worlds.edit', compact('adventureWorld', 'subjects'));
    }

    public function update(Request $request, AdventureWorld $adventureWorld)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:adventure_worlds,slug,' . $adventureWorld->id,
            'subject_id' => 'nullable|exists:subjects,id',
            'theme_color' => 'required|string|max:20',
            'icon' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_locked' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_locked'] = $request->boolean('is_locked');
        $adventureWorld->update($validated);

        return redirect()->route('admin.adventure-worlds.index')
            ->with('success', 'Adventure World updated successfully!');
    }

    public function destroy(AdventureWorld $adventureWorld)
    {
        $adventureWorld->delete();

        return redirect()->route('admin.adventure-worlds.index')
            ->with('success', 'Adventure World deleted successfully!');
    }

    public function show(AdventureWorld $adventureWorld)
    {
        $adventureWorld->load(['missions.lesson']);
        
        $availableMissions = Mission::where(function($q) use ($adventureWorld) {
            $q->whereNull('adventure_world_id')
              ->orWhere('adventure_world_id', '!=', $adventureWorld->id);
        })->with('lesson')->orderByDesc('id')->get();

        $lessons = Lesson::orderBy('title')->get(['id', 'slug', 'title']);

        return view('admin.adventure-worlds.show', compact('adventureWorld', 'availableMissions', 'lessons'));
    }

    public function assignMissions(Request $request, AdventureWorld $adventureWorld)
    {
        $request->validate([
            'mission_ids' => 'required|array',
            'mission_ids.*' => 'exists:missions,id',
        ]);

        Mission::whereIn('id', $request->mission_ids)->update(['adventure_world_id' => $adventureWorld->id]);

        return back()->with('success', count($request->mission_ids) . ' mission(s) added to the world!');
    }

    public function removeMission(AdventureWorld $adventureWorld, Mission $mission)
    {
        $mission->update(['adventure_world_id' => null]);
        return back()->with('success', 'Mission removed from the world.');
    }

    public function move(Request $request, AdventureWorld $adventureWorld)
    {
        $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $currentOrder = $adventureWorld->sort_order;

        if ($request->direction === 'up') {
            $swapWith = AdventureWorld::where('sort_order', '<', $currentOrder)
                ->orderBy('sort_order', 'desc')
                ->first();
        } else {
            $swapWith = AdventureWorld::where('sort_order', '>', $currentOrder)
                ->orderBy('sort_order', 'asc')
                ->first();
        }

        if ($swapWith) {
            $adventureWorld->update(['sort_order' => $swapWith->sort_order]);
            $swapWith->update(['sort_order' => $currentOrder]);
            return back()->with('success', 'World moved successfully.');
        }

        return back();
    }
}
