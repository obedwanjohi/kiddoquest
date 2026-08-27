<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $levelId = $request->integer('level') ?: null;

        $query = Subject::with('level')
            ->withCount('topics')
            ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
            ->orderBy('level_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        $subjects = $showTrashed
            ? $query->onlyTrashed()->get()
            : $query->get();

        $levels = Level::orderBy('sort_order')->orderBy('name')->get();
        $trashedCount = Subject::onlyTrashed()
            ->when($levelId, fn ($q) => $q->where('level_id', $levelId))
            ->count();

        return view('admin.subjects.index', compact('subjects', 'levels', 'levelId', 'showTrashed', 'trashedCount'));
    }

    public function create(Request $request)
    {
        $levels = Level::orderBy('sort_order')->orderBy('name')->get();
        $selectedLevelId = $request->integer('level_id') ?: null;

        return view('admin.subjects.create', compact('levels', 'selectedLevelId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSubject($request);

        $validated['created_by'] = auth('admin')->id();
        // Append to the end of the target level's list.
        $validated['sort_order'] = (int) Subject::where('level_id', $validated['level_id'])->max('sort_order') + 1;

        $subject = Subject::create($validated);

        return redirect()
            ->to($this->redirectTarget($request, $subject))
            ->with('success', "Subject \"{$subject->name}\" created successfully!");
    }

    public function show(Request $request, Subject $subject)
    {
        $showArchived = $request->boolean('archived');
        $search = trim((string) $request->input('q'));

        $subject->load('level.curriculum');

        // "Sub-Strands" are Topic records (repurposed per Module 3).
        $subStrands = $subject->topics()
            ->when($showArchived, fn ($q) => $q->onlyTrashed())
            ->when($search !== '', fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $archivedCount = $subject->topics()->onlyTrashed()->count();

        return view('admin.subjects.show', compact('subject', 'subStrands', 'showArchived', 'search', 'archivedCount'));
    }

    public function edit(Subject $subject)
    {
        $levels = Level::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.subjects.edit', compact('subject', 'levels'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $this->validateSubject($request, $subject);

        $subject->update($validated);

        return redirect()
            ->to($this->redirectTarget($request, $subject))
            ->with('success', "Subject \"{$subject->name}\" updated successfully!");
    }

    /**
     * Archive (soft-delete) the subject. Reversible via restore().
     */
    public function destroy(Request $request, Subject $subject)
    {
        $name = $subject->name;
        $level = $subject->level;
        $subject->delete();

        return redirect()
            ->to($this->redirectTarget($request, null, $level))
            ->with('success', "Subject \"{$name}\" archived.");
    }

    public function restore(Request $request, int $id)
    {
        $subject = Subject::onlyTrashed()->findOrFail($id);
        $subject->restore();

        return redirect()
            ->to($this->redirectTarget($request, $subject))
            ->with('success', "Subject \"{$subject->name}\" restored.");
    }

    /**
     * Permanently delete. Blocked while the subject still owns topics.
     */
    public function forceDelete(Request $request, int $id)
    {
        $subject = Subject::onlyTrashed()->withCount('topics')->findOrFail($id);

        if ($subject->topics_count > 0) {
            return back()->with('error', "Cannot permanently delete \"{$subject->name}\" — it still has {$subject->topics_count} topic(s). Remove those first.");
        }

        $name = $subject->name;
        $subject->forceDelete();

        return back()->with('success', "Subject \"{$name}\" permanently deleted.");
    }

    /**
     * Reorder a subject up/down within its own level (swaps with the neighbour).
     */
    public function move(Request $request, Subject $subject)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = Subject::where('level_id', $subject->level_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Normalise to guaranteed-distinct sequential order first.
        foreach ($siblings as $i => $sib) {
            if ((int) $sib->sort_order !== $i) {
                $sib->sort_order = $i;
                $sib->save();
            }
        }

        $pos = $siblings->search(fn ($s) => $s->id === $subject->id);
        $swapPos = $direction === 'up' ? $pos - 1 : $pos + 1;

        if ($pos !== false && isset($siblings[$swapPos])) {
            $a = $siblings[$pos];
            $b = $siblings[$swapPos];
            [$a->sort_order, $b->sort_order] = [$b->sort_order, $a->sort_order];
            $a->save();
            $b->save();
        }

        return back()->with('success', "Order updated.");
    }

    private function validateSubject(Request $request, ?Subject $subject = null): array
    {
        $slugRule = 'nullable|string|max:255|unique:subjects,slug';
        if ($subject) {
            $slugRule .= ',' . $subject->id;
        }

        return $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:20',
            'status' => 'required|in:draft,published,archived',
        ]);
    }

    /**
     * After a write, return to the originating level page when the request
     * came from there (return_to=level), otherwise the global subjects list.
     */
    private function redirectTarget(Request $request, ?Subject $subject = null, ?Level $level = null): string
    {
        $level = $level ?? $subject?->level;

        if ($request->input('return_to') === 'level' && $level) {
            return route('admin.levels.show', $level);
        }

        return route('admin.subjects.index');
    }
}
