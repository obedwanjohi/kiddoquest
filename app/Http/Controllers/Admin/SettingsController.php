<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $quizTypes = QuizType::orderBy('sort_order')->get();

        return view('admin.settings.index', compact('quizTypes'));
    }

    public function updateQuizType(Request $request, QuizType $quizType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:quiz_types,slug,' . $quizType->id,
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $quizType->update($validated);

        return back()->with('success', "Quiz type \"{$quizType->name}\" updated.");
    }

    public function toggleQuizType(QuizType $quizType): RedirectResponse
    {
        $quizType->update(['is_active' => ! $quizType->is_active]);

        $status = $quizType->fresh()->is_active ? 'enabled' : 'disabled';

        return back()->with('success', "Quiz type \"{$quizType->name}\" {$status}.");
    }
}