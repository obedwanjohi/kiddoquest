<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentAuditLog;
use App\Models\Lesson;
use App\Models\Topic;
use App\Models\Voice;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request)
    {
        $query = Lesson::with('topic.subject', 'creator', 'reviewer');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $lessons = $query->orderBy('sort_order')->orderBy('title')->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Lesson::count(),
            'draft' => Lesson::where('status', 'draft')->count(),
            'in_review' => Lesson::where('status', 'in_review')->count(),
            'published' => Lesson::where('status', 'published')->count(),
            'archived' => Lesson::where('status', 'archived')->count(),
        ];

        return view('admin.lessons.index', compact('lessons', 'statusCounts'));
    }

    public function create(Request $request)
    {
        $topics = Topic::with('subject.level')->orderBy('name')->get();
        $voices = Voice::active()->orderBy('sort_order')->orderBy('name')->get();
        $selectedTopicId = $request->integer('topic_id') ?: null;

        return view('admin.lessons.create', compact('topics', 'voices', 'selectedTopicId'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateLesson($request);

        $validated['created_by'] = auth('admin')->id();
        // Append to the end of the sub-strand's lesson list unless a position was given.
        $validated['sort_order'] = $request->filled('sort_order')
            ? (int) $request->input('sort_order')
            : (int) Lesson::where('topic_id', $validated['topic_id'])->max('sort_order') + 1;

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }
        if ($validated['status'] === 'in_review') {
            $validated['submitted_at'] = now();
        }

        $lesson = Lesson::create($validated);

        ContentAuditLog::log('Lesson', $lesson->id, 'created', null, $lesson->status, "Lesson \"{$lesson->title}\" created");

        return redirect()
            ->to($this->redirectTarget($request, $lesson))
            ->with('success', "Lesson \"{$lesson->title}\" created successfully!");
    }

    public function show(Lesson $lesson)
    {
        $lesson->load(['topic.subject.level', 'voice', 'creator', 'reviewer', 'auditLogs.admin']);
        return view('admin.lessons.show', compact('lesson'));
    }

    public function edit(Lesson $lesson)
    {
        $topics = Topic::with('subject.level')->orderBy('name')->get();
        $voices = Voice::active()->orderBy('sort_order')->orderBy('name')->get();

        // Ensure a currently-selected but now-inactive voice still appears in the list.
        if ($lesson->narration_voice_id && ! $voices->contains('id', $lesson->narration_voice_id)) {
            $voices = $voices->push(Voice::find($lesson->narration_voice_id))->filter();
        }

        return view('admin.lessons.edit', compact('lesson', 'topics', 'voices'));
    }

    public function update(Request $request, Lesson $lesson)
    {
        $validated = $this->validateLesson($request, $lesson);

        $validated['sort_order'] = $request->filled('sort_order') ? (int) $request->input('sort_order') : $lesson->sort_order;
        $oldStatus = $lesson->status;

        // Set workflow timestamps on transitions
        if ($validated['status'] !== $oldStatus) {
            if ($validated['status'] === 'published') {
                $validated['published_at'] = now();
                $validated['reviewed_by'] = auth('admin')->id();
                $validated['reviewed_at'] = now();
            }
            if ($validated['status'] === 'in_review') {
                $validated['submitted_at'] = now();
                $validated['reviewed_by'] = null;
                $validated['reviewed_at'] = null;
            }
            if ($validated['status'] === 'archived') {
                $validated['archived_at'] = now();
            }
            if ($validated['status'] === 'draft') {
                $validated['submitted_at'] = null;
                $validated['reviewed_by'] = null;
                $validated['reviewed_at'] = null;
            }
        }

        $lesson->update($validated);

        if ($validated['status'] !== $oldStatus) {
            ContentAuditLog::log('Lesson', $lesson->id, 'updated', $oldStatus, $validated['status'], "Status changed via edit");
        }

        return redirect()
            ->to($this->redirectTarget($request, $lesson))
            ->with('success', "Lesson \"{$lesson->title}\" updated successfully!");
    }

    // ── Workflow Actions ───────────────────────────────────────

    public function submitForReview(Lesson $lesson)
    {
        if ($lesson->status !== 'draft') {
            return back()->with('error', 'Only draft lessons can be submitted for review.');
        }

        $lesson->update([
            'status' => 'in_review',
            'submitted_at' => now(),
        ]);

        ContentAuditLog::log('Lesson', $lesson->id, 'submitted', 'draft', 'in_review');

        return back()->with('success', "Lesson \"{$lesson->title}\" submitted for review.");
    }

    public function approve(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'review_notes' => 'nullable|string|max:2000',
        ]);

        if ($lesson->status !== 'in_review') {
            return back()->with('error', 'Only lessons in review can be approved.');
        }

        $lesson->update([
            'status' => 'published',
            'published_at' => now(),
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'],
            'rejection_reason' => null,
        ]);

        ContentAuditLog::log('Lesson', $lesson->id, 'approved', 'in_review', 'published', $validated['review_notes']);

        return back()->with('success', "Lesson \"{$lesson->title}\" approved & published!");
    }

    public function reject(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        if ($lesson->status !== 'in_review') {
            return back()->with('error', 'Only lessons in review can be rejected.');
        }

        $lesson->update([
            'status' => 'draft',
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        ContentAuditLog::log('Lesson', $lesson->id, 'rejected', 'in_review', 'draft', $validated['rejection_reason']);

        return back()->with('success', "Lesson \"{$lesson->title}\" sent back to draft.");
    }

    public function archive(Lesson $lesson)
    {
        if ($lesson->status === 'archived') {
            return back()->with('error', 'Lesson is already archived.');
        }

        $oldStatus = $lesson->status;

        $lesson->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);

        ContentAuditLog::log('Lesson', $lesson->id, 'archived', $oldStatus, 'archived');

        return back()->with('success', "Lesson \"{$lesson->title}\" archived.");
    }

    public function unarchive(Lesson $lesson)
    {
        if ($lesson->status !== 'archived') {
            return back()->with('error', 'Only archived lessons can be restored.');
        }

        $lesson->update([
            'status' => 'draft',
            'archived_at' => null,
        ]);

        ContentAuditLog::log('Lesson', $lesson->id, 'updated', 'archived', 'draft', 'Unarchived');

        return back()->with('success', "Lesson \"{$lesson->title}\" restored to draft.");
    }

    public function destroy(Lesson $lesson)
    {
        $title = $lesson->title;
        $lesson->delete();

        ContentAuditLog::log('Lesson', $lesson->id, 'deleted', null, null, "Lesson \"{$title}\" deleted");

        return redirect()
            ->route('admin.lessons.index')
            ->with('success', "Lesson \"{$title}\" deleted.");
    }

    /**
     * Bulk actions: publish, archive, delete
     */
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:publish,archive,delete',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:lessons,id',
        ]);

        $lessons = Lesson::whereIn('id', $validated['ids'])->get();
        $count = $lessons->count();
        $action = $validated['action'];

        foreach ($lessons as $lesson) {
            $oldStatus = $lesson->status;

            switch ($action) {
                case 'publish':
                    $lesson->update([
                        'status' => 'published',
                        'published_at' => now(),
                        'reviewed_by' => auth('admin')->id(),
                        'reviewed_at' => now(),
                    ]);
                    ContentAuditLog::log('Lesson', $lesson->id, 'published', $oldStatus, 'published', 'Bulk published');
                    break;

                case 'archive':
                    $lesson->update([
                        'status' => 'archived',
                        'archived_at' => now(),
                    ]);
                    ContentAuditLog::log('Lesson', $lesson->id, 'archived', $oldStatus, 'archived', 'Bulk archived');
                    break;

                case 'delete':
                    $lesson->delete();
                    ContentAuditLog::log('Lesson', $lesson->id, 'deleted', $oldStatus, null, 'Bulk deleted');
                    break;
            }
        }

        $messages = [
            'publish' => "{$count} lessons published.",
            'archive' => "{$count} lessons archived.",
            'delete' => "{$count} lessons deleted.",
        ];

        return back()->with('success', $messages[$action]);
    }

    /**
     * Reorder a lesson up/down within its own sub-strand (swaps with neighbour).
     */
    public function move(Request $request, Lesson $lesson)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';

        $siblings = Lesson::where('topic_id', $lesson->topic_id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        foreach ($siblings as $i => $sib) {
            if ((int) $sib->sort_order !== $i) {
                $sib->sort_order = $i;
                $sib->save();
            }
        }

        $pos = $siblings->search(fn ($l) => $l->id === $lesson->id);
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

    // ── Helpers ────────────────────────────────────────────────

    private function validateLesson(Request $request, ?Lesson $lesson = null): array
    {
        return $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:lessons,slug' . ($lesson ? ',' . $lesson->id : ''),
            'summary' => 'nullable|string|max:500',
            'learning_objective' => 'nullable|string|max:1000',
            'intro_narration_text' => 'nullable|string|max:2000',
            'summary_narration_text' => 'nullable|string|max:2000',
            'narration_voice_id' => 'nullable|exists:voices,id',
            'content' => 'nullable|string',
            'content_type' => 'required|in:text,video,interactive',
            'video_url' => 'nullable|string|max:500',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_media_id' => 'nullable|exists:media,id',
            'duration_minutes' => 'nullable|integer|min:1|max:300',
            'status' => 'required|in:draft,in_review,published,archived',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    /**
     * Return to the parent sub-strand page when the edit came from there,
     * otherwise the lesson detail page.
     */
    private function redirectTarget(Request $request, Lesson $lesson): string
    {
        if ($request->input('return_to') === 'subStrand' && $lesson->topic) {
            return route('admin.topics.show', $lesson->topic);
        }

        return route('admin.lessons.show', $lesson);
    }
}
