<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ContentAuditLog;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // ── Core Counts ──
        $stats = [
            'subjects' => Subject::count(),
            'subjects_published' => Subject::where('status', 'published')->count(),
            'topics' => Topic::count(),
            'topics_published' => Topic::where('status', 'published')->count(),
            'lessons' => Lesson::count(),
            'lessons_draft' => Lesson::where('status', 'draft')->count(),
            'lessons_published' => Lesson::where('status', 'published')->count(),
            'lessons_in_review' => Lesson::where('status', 'in_review')->count(),
            'lessons_archived' => Lesson::where('status', 'archived')->count(),
            'quizzes' => Quiz::count(),
            'questions' => QuizQuestion::count(),
            'quiz_types' => QuizType::where('is_active', true)->count(),
            'media_items' => Media::count(),
            'admins' => Admin::count(),
        ];

        // ── Publishing Pipeline ──
        $pipeline = [
            'draft' => Lesson::where('status', 'draft')->count(),
            'in_review' => Lesson::where('status', 'in_review')->count(),
            'published' => Lesson::where('status', 'published')->count(),
            'archived' => Lesson::where('status', 'archived')->count(),
        ];

        // ── Coverage Gap Analysis ──
        $coverage = [
            'subjects_without_topics' => Subject::whereDoesntHave('topics')->count(),
            'topics_without_lessons' => Topic::whereDoesntHave('lessons')->count(),
            'lessons_without_video' => Lesson::where(function ($q) { $q->whereNull('video_url')->orWhere('video_url', ''); })->count(),
            'lessons_without_quiz' => Lesson::whereDoesntHave('quizzes')->count(),
        ];

        // ── Subject Coverage Breakdown ──
        $subjectCoverage = Subject::withCount(['topics', 'lessons'])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($s) {
                $s->publish_rate = $s->lessons_count > 0
                    ? round(($s->lessons()->where('lessons.status', 'published')->count() / $s->lessons_count) * 100)
                    : 0;
                return $s;
            });

        // ── Recent Lessons (all statuses) ──
        $recentLessons = Lesson::with(['topic.subject', 'creator'])
            ->latest()
            ->limit(8)
            ->get();

        // ── Recent Audit Activity ──
        $recentActivity = ContentAuditLog::with(['admin', 'entity'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'pipeline',
            'coverage',
            'subjectCoverage',
            'recentLessons',
            'recentActivity'
        ));
    }
}