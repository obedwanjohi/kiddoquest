<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdventureWorld;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\Media;
use App\Models\Mission;
use App\Models\QuestionBank;
use App\Models\Subject;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    public function globalIndex(Request $request)
    {
        $query = Mission::with(['lesson.topic.subject.level', 'questionBank', 'thumbnailMedia']);

        // Filters
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }
        if ($request->filled('subject_id')) {
            $query->whereHas('lesson.topic.subject', fn($q) => $q->where('subjects.id', $request->subject_id));
        }
        if ($request->filled('level_id')) {
            $query->whereHas('lesson.topic.subject.level', fn($q) => $q->where('levels.id', $request->level_id));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $missions = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $lessons = Lesson::orderBy('title')->get(['id', 'title']);
        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        $levels = Level::orderBy('sort_order')->get(['id', 'name']);

        return view('admin.missions.global-index', compact('missions', 'lessons', 'subjects', 'levels'));
    }

    public function index(Request $request, ?Lesson $lesson = null)
    {
        if ($lesson && $lesson->exists) {
            $lesson->load(['missions.questionBank', 'missions.thumbnailMedia', 'topic.subject']);
            return view('admin.missions.index', compact('lesson'));
        }

        return $this->globalIndex($request);
    }

    public function create(Lesson $lesson)
    {
        $questionBanks = QuestionBank::orderBy('name')->get(['id', 'name']);
        $adventureWorlds = AdventureWorld::orderBy('sort_order')->get(['id', 'name']);
        $preselectedWorldId = request('adventure_world_id');
        return view('admin.missions.create', compact('lesson', 'questionBanks', 'adventureWorlds', 'preselectedWorldId'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        // Convert empty strings from Media Picker to null
        $request->merge([
            'thumbnail_media_id' => $request->filled('thumbnail_media_id') ? $request->thumbnail_media_id : null,
            'video_media_id' => $request->filled('video_media_id') ? $request->video_media_id : null,
            'question_bank_id' => $request->filled('question_bank_id') ? $request->question_bank_id : null,
            'adventure_world_id' => $request->filled('adventure_world_id') ? $request->adventure_world_id : null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'display_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|string',
            'intro_narration_text' => 'nullable|string',
            'intro_voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'outro_narration_text' => 'nullable|string',
            'outro_voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'question_bank_id' => 'nullable|exists:question_banks,id',
            'adventure_world_id' => 'nullable|exists:adventure_worlds,id',
            'allow_replay' => 'boolean',
            'pass_threshold_percent' => 'nullable|integer|min:0|max:100',
            'stars_reward' => 'nullable|integer|min:1|max:5',
            'questions_per_session' => 'nullable|integer|min:1|max:50',
            'randomize_questions' => 'boolean',
            'estimated_minutes' => 'nullable|integer|min:1|max:120',
            'status' => 'required|in:draft,in_review,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['lesson_id'] = $lesson->id;
        $validated['created_by'] = auth('admin')->id();
        $validated['allow_replay'] = $request->boolean('allow_replay', true);
        $validated['randomize_questions'] = $request->boolean('randomize_questions', true);
        $validated['sort_order'] = $validated['sort_order'] ?? $lesson->missions()->count();

        $mission = $lesson->missions()->create($validated);

        return redirect()->route('admin.lessons.missions.index', $lesson)
            ->with('success', "Mission \"{$validated['title']}\" created!");
    }

    public function show(Lesson $lesson, Mission $mission)
    {
        $mission->load(['questionBank.questions', 'thumbnailMedia', 'videoMedia', 'lesson.topic.subject']);
        return view('admin.missions.show', compact('lesson', 'mission'));
    }

    public function edit(Lesson $lesson, Mission $mission)
    {
        $questionBanks = QuestionBank::orderBy('name')->get(['id', 'name']);
        $adventureWorlds = AdventureWorld::orderBy('sort_order')->get(['id', 'name']);
        $mission->load(['thumbnailMedia', 'videoMedia']);
        return view('admin.missions.edit', compact('lesson', 'mission', 'questionBanks', 'adventureWorlds'));
    }

    public function update(Request $request, Lesson $lesson, Mission $mission)
    {
        // Convert empty strings from Media Picker to null
        $request->merge([
            'thumbnail_media_id' => $request->filled('thumbnail_media_id') ? $request->thumbnail_media_id : null,
            'video_media_id' => $request->filled('video_media_id') ? $request->video_media_id : null,
            'question_bank_id' => $request->filled('question_bank_id') ? $request->question_bank_id : null,
            'adventure_world_id' => $request->filled('adventure_world_id') ? $request->adventure_world_id : null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'display_title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|string',
            'intro_narration_text' => 'nullable|string',
            'intro_voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'outro_narration_text' => 'nullable|string',
            'outro_voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'question_bank_id' => 'nullable|exists:question_banks,id',
            'adventure_world_id' => 'nullable|exists:adventure_worlds,id',
            'allow_replay' => 'boolean',
            'pass_threshold_percent' => 'nullable|integer|min:0|max:100',
            'stars_reward' => 'nullable|integer|min:1|max:5',
            'questions_per_session' => 'nullable|integer|min:1|max:50',
            'randomize_questions' => 'boolean',
            'estimated_minutes' => 'nullable|integer|min:1|max:120',
            'status' => 'required|in:draft,in_review,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['allow_replay'] = $request->boolean('allow_replay', true);
        $validated['randomize_questions'] = $request->boolean('randomize_questions', true);
        $mission->update($validated);

        return redirect()->route('admin.lessons.missions.index', $lesson)
            ->with('success', "Mission \"{$validated['title']}\" updated!");
    }

    public function destroy(Lesson $lesson, Mission $mission)
    {
        $title = $mission->title;
        $mission->delete();

        return redirect()->route('admin.lessons.missions.index', $lesson)
            ->with('success', "Mission \"{$title}\" deleted.");
    }

    public function duplicate(Lesson $lesson, Mission $mission)
    {
        $newMission = $mission->replicate();
        $newMission->title = $mission->title . ' (Copy)';
        $newMission->slug = null; // Will be auto-generated
        $newMission->sort_order = $lesson->missions()->count();
        $newMission->save();

        return redirect()->route('admin.lessons.missions.index', $lesson)
            ->with('success', 'Mission duplicated!');
    }
}