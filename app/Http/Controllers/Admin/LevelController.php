<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');

        $query = Level::with('curriculum')
            ->withCount('subjects')
            ->orderBy('sort_order')
            ->orderBy('name');

        $levels = $showTrashed
            ? $query->onlyTrashed()->get()
            : $query->get();

        $trashedCount = Level::onlyTrashed()->count();

        return view('admin.levels.index', compact('levels', 'showTrashed', 'trashedCount'));
    }

    public function create()
    {
        $curricula = $this->curriculaOptions();

        return view('admin.levels.create', compact('curricula'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateLevel($request);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $level = Level::create($validated);

        return redirect()
            ->route('admin.levels.index')
            ->with('success', "Level \"{$level->name}\" created successfully!");
    }

    public function show(Request $request, Level $level)
    {
        $showArchived = $request->boolean('archived');
        $level->load('curriculum');

        $subjects = $level->subjects()
            ->when($showArchived, fn ($q) => $q->onlyTrashed())
            ->withCount('topics')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $archivedCount = $level->subjects()->onlyTrashed()->count();

        return view('admin.levels.show', compact('level', 'subjects', 'showArchived', 'archivedCount'));
    }

    public function edit(Level $level)
    {
        $curricula = $this->curriculaOptions();

        return view('admin.levels.edit', compact('level', 'curricula'));
    }

    public function update(Request $request, Level $level)
    {
        $validated = $this->validateLevel($request, $level);

        $level->update($validated);

        return redirect()
            ->route('admin.levels.index')
            ->with('success', "Level \"{$level->name}\" updated successfully!");
    }

    /**
     * Soft-delete (send to trash). Reversible via restore().
     */
    public function destroy(Level $level)
    {
        $name = $level->name;
        $level->delete();

        return redirect()
            ->route('admin.levels.index')
            ->with('success', "Level \"{$name}\" moved to trash.");
    }

    public function restore(int $id)
    {
        $level = Level::onlyTrashed()->findOrFail($id);
        $level->restore();

        return redirect()
            ->route('admin.levels.index', ['trashed' => 1])
            ->with('success', "Level \"{$level->name}\" restored.");
    }

    /**
     * Permanently delete. Blocked while the level still owns subjects,
     * to avoid orphaning them.
     */
    public function forceDelete(int $id)
    {
        $level = Level::onlyTrashed()->withCount('subjects')->findOrFail($id);

        if ($level->subjects_count > 0) {
            return redirect()
                ->route('admin.levels.index', ['trashed' => 1])
                ->with('error', "Cannot permanently delete \"{$level->name}\" — it still has {$level->subjects_count} subject(s). Move or delete those first.");
        }

        $name = $level->name;
        $level->forceDelete();

        return redirect()
            ->route('admin.levels.index', ['trashed' => 1])
            ->with('success', "Level \"{$name}\" permanently deleted.");
    }

    /**
     * Reorder a level up/down within its own curriculum (swaps with the neighbour).
     */
    public function move(Request $request, Level $level)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = Level::where('curriculum_id', $level->curriculum_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($siblings as $i => $sib) {
            if ((int) $sib->sort_order !== $i) {
                $sib->sort_order = $i;
                $sib->save();
            }
        }

        $pos = $siblings->search(fn ($l) => $l->id === $level->id);
        $swapPos = $direction === 'up' ? $pos - 1 : $pos + 1;

        if ($pos !== false && isset($siblings[$swapPos])) {
            $a = $siblings[$pos];
            $b = $siblings[$swapPos];
            [$a->sort_order, $b->sort_order] = [$b->sort_order, $a->sort_order];
            $a->save();
            $b->save();
        }

        return back()->with('success', 'Order updated.');
    }

    private function curriculaOptions()
    {
        return Curriculum::orderBy('sort_order')->orderBy('name')->get();
    }

    private function validateLevel(Request $request, ?Level $level = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:levels,slug';
        if ($level) {
            $slugRule .= ',' . $level->id;
        }

        return $request->validate([
            'curriculum_id' => 'required|exists:curricula,id',
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'stage' => 'nullable|string|max:30',
            'min_age' => 'nullable|integer|min:0|max:18',
            'max_age' => 'nullable|integer|min:0|max:18|gte:min_age',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'status' => 'required|in:draft,published,archived',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
