<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Sub-Strand admin controller.
 *
 * NOTE: the underlying table/model is still called "Topic" (repurposed as the
 * Sub-Strand layer per Module 3 — all lessons attach via topic_id). The UI
 * presents these records as "Sub-Strands".
 */
class TopicController extends Controller
{
    public function index(Request $request)
    {
        $showTrashed = $request->boolean('trashed');
        $subjectId = $request->integer('subject') ?: null;
        $search = trim((string) $request->input('q'));

        $query = Topic::with('subject.level')
            ->withCount('lessons')
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->when($search !== '', fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->orderBy('subject_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        $topics = $showTrashed ? $query->onlyTrashed()->get() : $query->get();

        $subjects = Subject::with('level')->orderBy('name')->get();
        $trashedCount = Topic::onlyTrashed()
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->count();

        return view('admin.topics.index', compact('topics', 'subjects', 'subjectId', 'search', 'showTrashed', 'trashedCount'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::with('level')->orderBy('name')->get();
        $selectedSubjectId = $request->integer('subject_id') ?: null;

        return view('admin.topics.create', compact('subjects', 'selectedSubjectId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateTopic($request);

        $validated['created_by'] = auth('admin')->id();
        $validated['sort_order'] = (int) Topic::where('subject_id', $validated['subject_id'])->max('sort_order') + 1;

        $topic = Topic::create($validated);

        return redirect()
            ->to($this->redirectTarget($request, $topic))
            ->with('success', "Sub-Strand \"{$topic->name}\" created successfully!");
    }

    public function show(Topic $topic)
    {
        $topic->load(['subject.level', 'lessons' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return view('admin.topics.show', compact('topic'));
    }

    public function edit(Topic $topic)
    {
        $subjects = Subject::with('level')->orderBy('name')->get();

        return view('admin.topics.edit', compact('topic', 'subjects'));
    }

    public function update(Request $request, Topic $topic)
    {
        $validated = $this->validateTopic($request, $topic);

        $topic->update($validated);

        return redirect()
            ->to($this->redirectTarget($request, $topic))
            ->with('success', "Sub-Strand \"{$topic->name}\" updated successfully!");
    }

    /**
     * Archive (soft-delete). Reversible via restore().
     */
    public function destroy(Request $request, Topic $topic)
    {
        $name = $topic->name;
        $subject = $topic->subject;
        $topic->delete();

        return redirect()
            ->to($this->redirectTarget($request, null, $subject))
            ->with('success', "Sub-Strand \"{$name}\" archived.");
    }

    public function restore(Request $request, int $id)
    {
        $topic = Topic::onlyTrashed()->findOrFail($id);
        $topic->restore();

        return redirect()
            ->to($this->redirectTarget($request, $topic))
            ->with('success', "Sub-Strand \"{$topic->name}\" restored.");
    }

    /**
     * Permanently delete. Blocked while the sub-strand still owns lessons.
     */
    public function forceDelete(Request $request, int $id)
    {
        $topic = Topic::onlyTrashed()->withCount('lessons')->findOrFail($id);

        if ($topic->lessons_count > 0) {
            return back()->with('error', "Cannot permanently delete \"{$topic->name}\" — it still has {$topic->lessons_count} lesson(s). Remove those first.");
        }

        $name = $topic->name;
        $topic->forceDelete();

        return back()->with('success', "Sub-Strand \"{$name}\" permanently deleted.");
    }

    /**
     * Reorder a sub-strand up/down within its own subject (swaps with neighbour).
     */
    public function move(Request $request, Topic $topic)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = Topic::where('subject_id', $topic->subject_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($siblings as $i => $sib) {
            if ((int) $sib->sort_order !== $i) {
                $sib->sort_order = $i;
                $sib->save();
            }
        }

        $pos = $siblings->search(fn ($t) => $t->id === $topic->id);
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

    private function validateTopic(Request $request, ?Topic $topic = null): array
    {
        $subjectId = $request->integer('subject_id');

        return $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            // Prevent duplicate sub-strand names within the SAME subject.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('topics', 'name')
                    ->where(fn ($q) => $q->where('subject_id', $subjectId)->whereNull('deleted_at'))
                    ->ignore($topic?->id),
            ],
            'slug' => 'nullable|string|max:255|unique:topics,slug' . ($topic ? ',' . $topic->id : ''),
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:10',
            'status' => 'required|in:draft,published,archived',
        ], [
            'name.unique' => 'A sub-strand with this name already exists in this subject.',
        ]);
    }

    private function redirectTarget(Request $request, ?Topic $topic = null, ?Subject $subject = null): string
    {
        $subject = $subject ?? $topic?->subject;

        if ($request->input('return_to') === 'subject' && $subject) {
            return route('admin.subjects.show', $subject);
        }

        return route('admin.topics.index');
    }
}
