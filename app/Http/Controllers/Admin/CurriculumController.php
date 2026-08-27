<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Level;
use App\Models\Subject;
use App\Models\SubStrand;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    /**
     * Display the curriculum hierarchy overview.
     */
    public function index()
    {
        $levels = Level::withCount(['subjects'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.curriculum.index', compact('levels'));
    }

    /**
     * Show a single level with its subjects and sub-strands.
     */
    public function showLevel(Level $level)
    {
        $level->load([
            'subjects' => function ($q) {
                $q->withCount(['lessons', 'topics', 'subStrands'])
                  ->orderBy('name');
            },
        ]);

        return view('admin.curriculum.level', compact('level'));
    }

    /**
     * Show a subject with its sub-strands and lessons.
     */
    public function showSubject(Subject $subject)
    {
        $subject->load([
            'level',
            'subStrands' => function ($q) {
                $q->withCount('lessons')->orderBy('sort_order');
            },
            'topics.lessons',
            'lessons' => function ($q) {
                $q->with('subStrand', 'quiz.questions')->orderBy('sort_order');
            },
        ]);

        return view('admin.curriculum.subject', compact('subject'));
    }
}