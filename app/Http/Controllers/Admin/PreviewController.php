<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\View\View;

class PreviewController extends Controller
{
    /**
     * Preview a lesson exactly as a child will see it.
     * (Phase 9 — Content Preview / Bridge to Learning Side)
     */
    public function show(Lesson $lesson): View
    {
        $lesson->load([
            'topic.subject',
            'creator',
            'quizzes' => function ($q) {
                $q->where('status', '!=', 'archived')->orderBy('sort_order');
            },
            'quizzes.questions.quizType',
            'quizzes.questions.options' => function ($q) {
                $q->orderBy('sort_order');
            },
        ]);

        // Tally quiz info for the preview header
        $totalQuestions = $lesson->quizzes->sum(fn ($quiz) => $quiz->questions->count());

        return view('admin.preview.show', compact('lesson', 'totalQuestions'));
    }
}