<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Level;
use App\Models\QuestionBank;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use App\Models\SubStrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionBankController extends Controller
{
    /**
     * Display all question banks with search and filters.
     */
    public function index(Request $request)
    {
        $query = QuestionBank::with(['subject', 'subStrand', 'quizType', 'creator'])
            ->withCount(['questions', 'assignedQuestions']);

        // --- Search (name or description) ---
        if ($search = trim($request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // --- Filters ---
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->subject);
        }
        if ($request->filled('quiz_type')) {
            $query->where('quiz_type_id', $request->quiz_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        $banks = $query->orderBy('sort_order')->orderByDesc('id')->paginate(20)->withQueryString();

        // Data for filter dropdowns
        $subjects   = Subject::orderBy('name')->get();
        $quizTypes  = QuizType::orderBy('name')->get();
        $allQuestionBanks = QuestionBank::orderBy('name')->get(['id', 'name', 'subject_id']);

        return view('admin.question-banks.index', compact('banks', 'subjects', 'quizTypes', 'allQuestionBanks'));
    }

    /**
     * Show a single question bank (bank details only).
     */
    public function show(QuestionBank $questionBank)
    {
        $questionBank->load([
            'subject',
            'subStrand',
            'quizType',
            'creator',
            'questions.options',
            'assignedQuestions.options',
        ]);

        return view('admin.question-banks.show', compact('questionBank'));
    }

    /**
     * Show the form to create a new question bank.
     */
    public function create(Request $request)
    {
        $subjects  = Subject::orderBy('name')->get();
        $quizTypes = QuizType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.question-banks.create', compact('subjects', 'quizTypes'));
    }

    /**
     * Store a newly created question bank.
     */
    public function store(Request $request)
    {
        $validated = $this->validateBank($request);

        $validated['created_by'] = auth('admin')->id();

        $bank = QuestionBank::create($validated);

        return redirect()
            ->route('admin.question-banks.show', $bank)
            ->with('success', "✅ Question Bank '{$bank->name}' created!");
    }

    /**
     * Show the form to edit a question bank.
     */
    public function edit(QuestionBank $questionBank)
    {
        $subjects  = Subject::orderBy('name')->get();
        $quizTypes = QuizType::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
        $subStrands = SubStrand::orderBy('name')->get(['id', 'name']);

        return view('admin.question-banks.edit', compact('questionBank', 'subjects', 'quizTypes', 'subStrands'));
    }

    /**
     * Update a question bank.
     */
    public function update(Request $request, QuestionBank $questionBank)
    {
        $validated = $this->validateBank($request);

        $questionBank->update($validated);

        return redirect()
            ->route('admin.question-banks.show', $questionBank)
            ->with('success', "✅ Question Bank updated!");
    }

    /**
     * Duplicate a question bank (copies metadata, optionally questions).
     */
    public function duplicate(QuestionBank $questionBank)
    {
        $copy = DB::transaction(function () use ($questionBank) {
            $newBank = $questionBank->replicate([
                // Don't copy timestamps/primary key (replicate already excludes PK)
            ]);
            $newBank->name = $questionBank->name . ' (Copy)';
            $newBank->status = 'draft';
            $newBank->created_by = auth('admin')->id();
            $newBank->save();

            // Copy questions
            foreach ($questionBank->questions as $question) {
                $newQuestion = $question->replicate(['id']);
                $newQuestion->question_bank_id = $newBank->id;
                $newQuestion->quiz_id = null; // detach from any quiz
                $newQuestion->save();

                // Copy options
                foreach ($question->options as $option) {
                    $newOption = $option->replicate(['id']);
                    $newOption->question_id = $newQuestion->id;
                    $newOption->save();
                }
            }

            return $newBank;
        });

        return redirect()
            ->route('admin.question-banks.edit', $copy)
            ->with('success', "📋 Duplicated as '{$copy->name}'. Adjust details and save.");
    }

    /**
     * Preview a random draw from the bank (for admin testing).
     */
    public function preview(QuestionBank $questionBank)
    {
        $questionBank->load(['questions.options', 'questions.quizType', 'assignedQuestions.options', 'assignedQuestions.quizType']);

        $poolCount = $questionBank->pool_count;
        $drawCount = min(5, $poolCount); // Default to 5 for preview

        // Draw a fresh random selection every time
        $drawn = $questionBank->drawQuestions($drawCount);

        return view('admin.question-banks.preview', compact('questionBank', 'drawn'));
    }

    // ────────────────────────────────────────────────────────────
    //  MODULE 5.1 — Question Assignment & Management
    // ────────────────────────────────────────────────────────────

    /**
     * Manage Questions page: dual-panel (available ↔ assigned).
     */
    public function manageQuestions(Request $request, QuestionBank $questionBank)
    {
        $questionBank->load(['subject', 'subStrand', 'quizType']);

        // IDs already assigned to this bank (to exclude from "available")
        $assignedIds = $questionBank->assignedQuestions()->pluck('quiz_questions.id');

        // ── Build available-questions query with filters ──
        $availableQuery = QuizQuestion::query()
            ->with(['quizType', 'quiz.lesson.topic.subject'])
            ->whereNotIn('quiz_questions.id', $assignedIds);

        // Search
        if ($search = trim($request->get('search', ''))) {
            $availableQuery->where('prompt', 'like', "%{$search}%");
        }

        // Default scope: bank's subject if set
        $subjectFilter = $request->get('subject', $questionBank->subject_id);
        $levelFilter   = $request->get('level');

        // Join lesson→topic→subject→level for curriculum filtering
        $availableQuery->leftJoin('quizzes', 'quiz_questions.quiz_id', '=', 'quizzes.id')
            ->leftJoin('lessons', 'quizzes.lesson_id', '=', 'lessons.id')
            ->leftJoin('topics', 'lessons.topic_id', '=', 'topics.id')
            ->leftJoin('subjects', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('levels', 'subjects.level_id', '=', 'levels.id')
            ->select('quiz_questions.*');

        if ($subjectFilter) {
            $availableQuery->where('subjects.id', $subjectFilter);
        }
        if ($levelFilter) {
            $availableQuery->where('levels.id', $levelFilter);
        }
        if ($request->filled('lesson')) {
            $availableQuery->where('lessons.id', $request->lesson);
        }
        if ($request->filled('quiz_type')) {
            $availableQuery->where('quiz_questions.quiz_type_id', $request->quiz_type);
        }
        if ($request->filled('difficulty')) {
            $availableQuery->where('quiz_questions.difficulty', $request->difficulty);
        }

        $available = $availableQuery->orderByDesc('quiz_questions.id')->paginate(25, ['*'], 'available_page')->withQueryString();

        // ── Currently assigned questions ──
        $assigned = $questionBank->assignedQuestions()
            ->with(['quizType', 'quiz.lesson'])
            ->orderBy('prompt')
            ->get();

        // ── Filter dropdown data ──
        $subjects  = Subject::orderBy('name')->get();
        $levels    = Level::orderBy('sort_order')->orderBy('name')->get();
        $quizTypes = QuizType::orderBy('name')->get();
        $lessons   = Lesson::orderBy('title')->limit(200)->get(['id', 'title']);

        return view('admin.question-banks.manage-questions', compact(
            'questionBank', 'available', 'assigned', 'subjects', 'levels', 'quizTypes', 'lessons'
        ));
    }

    /**
     * Assign one or more questions to the bank (idempotent).
     */
    public function assignQuestions(Request $request, QuestionBank $questionBank)
    {
        $validated = $request->validate([
            'question_ids'   => 'required_without:assign_all_filtered|array',
            'question_ids.*' => 'exists:quiz_questions,id',
            'assign_all_filtered' => 'nullable|boolean',
        ]);

        // Determine which IDs to attach
        if (!empty($validated['assign_all_filtered'])) {
            // Re-run the filter query to get all matching IDs
            $questionIds = $this->getFilteredQuestionIds($request, $questionBank);
        } else {
            $questionIds = $validated['question_ids'];
        }

        if (empty($questionIds)) {
            return redirect()
                ->route('admin.question-banks.questions', $questionBank)
                ->with('info', 'No questions selected to assign.');
        }

        // Build pivot rows with sort_order (skip existing — syncWithoutDetaching)
        $nextOrder = DB::table('question_bank_questions')
            ->where('question_bank_id', $questionBank->id)
            ->max('sort_order') ?? 0;

        $pivotData = [];
        foreach ($questionIds as $qid) {
            $pivotData[$qid] = ['sort_order' => ++$nextOrder];
        }

        $attached = $questionBank->assignedQuestions()->syncWithoutDetaching($pivotData);

        $count = count($attached['attached'] ?? []);

        return redirect()
            ->route('admin.question-banks.questions', $questionBank)
            ->with('success', "✅ {$count} question(s) assigned to '{$questionBank->name}'.");
    }

    /**
     * Remove a single question from the bank (pivot only).
     */
    public function removeQuestion(Request $request, QuestionBank $questionBank, int $questionId)
    {
        $questionBank->assignedQuestions()->detach($questionId);

        return redirect()
            ->route('admin.question-banks.questions', $questionBank)
            ->with('success', '✅ Question removed from the bank.');
    }

    /**
     * Bulk-remove multiple questions from the bank (pivot only).
     */
    public function bulkRemove(Request $request, QuestionBank $questionBank)
    {
        $validated = $request->validate([
            'question_ids'   => 'required|array|min:1',
            'question_ids.*' => 'exists:quiz_questions,id',
        ]);

        $count = $questionBank->assignedQuestions()->detach($validated['question_ids']);

        return redirect()
            ->route('admin.question-banks.questions', $questionBank)
            ->with('success', "✅ {$count} question(s) removed from '{$questionBank->name}'.");
    }

    /**
     * Helper: re-run the current filter set to get all matching question IDs
     * (used by "Assign All Filtered").
     */
    private function getFilteredQuestionIds(Request $request, QuestionBank $questionBank): array
    {
        $assignedIds = $questionBank->assignedQuestions()->pluck('quiz_questions.id');

        $query = QuizQuestion::query()
            ->whereNotIn('quiz_questions.id', $assignedIds)
            ->leftJoin('quizzes', 'quiz_questions.quiz_id', '=', 'quizzes.id')
            ->leftJoin('lessons', 'quizzes.lesson_id', '=', 'lessons.id')
            ->leftJoin('topics', 'lessons.topic_id', '=', 'topics.id')
            ->leftJoin('subjects', 'topics.subject_id', '=', 'subjects.id')
            ->leftJoin('levels', 'subjects.level_id', '=', 'levels.id')
            ->select('quiz_questions.id');

        $subjectFilter = $request->get('subject', $questionBank->subject_id);

        if ($search = trim($request->get('search', ''))) {
            $query->where('prompt', 'like', "%{$search}%");
        }
        if ($subjectFilter) {
            $query->where('subjects.id', $subjectFilter);
        }
        if ($request->filled('level')) {
            $query->where('levels.id', $request->level);
        }
        if ($request->filled('lesson')) {
            $query->where('lessons.id', $request->lesson);
        }
        if ($request->filled('quiz_type')) {
            $query->where('quiz_questions.quiz_type_id', $request->quiz_type);
        }
        if ($request->filled('difficulty')) {
            $query->where('quiz_questions.difficulty', $request->difficulty);
        }

        return $query->pluck('quiz_questions.id')->toArray();
    }

    /**
     * Remove a question bank.
     */
    public function destroy(QuestionBank $questionBank)
    {
        $name = $questionBank->name;
        
        // Wipe questions & options belonging to this bank
        $qIds = \App\Models\QuizQuestion::whereIn('question_bank_id', [$questionBank->id])->pluck('id');
        \App\Models\QuestionOption::whereIn('question_id', $qIds)->delete();
        \Illuminate\Support\Facades\DB::table('question_bank_questions')->whereIn('question_bank_id', [$questionBank->id])->delete();
        \App\Models\QuizQuestion::whereIn('question_bank_id', [$questionBank->id])->forceDelete();
        
        $questionBank->delete();

        return redirect()
            ->route('admin.question-banks.index')
            ->with('success', "🗑️ Question Bank '{$name}' deleted.");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'exists:question_banks,id',
        ]);

        $ids = $validated['ids'];
        
        $qIds = \App\Models\QuizQuestion::whereIn('question_bank_id', $ids)->pluck('id');
        \App\Models\QuestionOption::whereIn('question_id', $qIds)->delete();
        \Illuminate\Support\Facades\DB::table('question_bank_questions')->whereIn('question_bank_id', $ids)->delete();
        \App\Models\QuizQuestion::whereIn('question_bank_id', $ids)->forceDelete();
        
        $count = QuestionBank::whereIn('id', $ids)->delete();

        return redirect()
            ->route('admin.question-banks.index')
            ->with('success', "🗑️ Successfully bulk deleted {$count} Question Bank(s)!");
    }

    /**
     * Import a CSV file to create OR append to a Question Bank.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file'          => 'required|file|mimes:csv,txt|max:10240',
            'question_bank_id' => 'nullable|exists:question_banks,id',
            'name'              => 'nullable|string|max:255',
            'subject_id'        => 'nullable|exists:subjects,id',
            'sub_strand_id'     => 'nullable|exists:sub_strands,id',
            'difficulty'        => 'nullable|in:easy,medium,hard',
            'status'            => 'nullable|in:draft,published',
        ]);

        $file = $request->file('csv_file');
        $importer = new \App\Services\QuestionBankCsvImporter();

        $meta = [
            'question_bank_id' => $request->input('question_bank_id'),
            'name' => $request->input('name') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'subject_id' => $request->input('subject_id'),
            'sub_strand_id' => $request->input('sub_strand_id'),
            'difficulty' => $request->input('difficulty', 'medium'),
            'status' => $request->input('status', 'published'),
            'created_by' => auth('admin')->id(),
        ];

        $result = $importer->import($file->getRealPath(), $meta);

        if (!$result['success'] || $result['imported_count'] === 0) {
            $errorMsg = !empty($result['errors']) ? implode(', ', $result['errors']) : 'No questions were found in the CSV.';
            return redirect()->back()->with('error', "❌ CSV Import Failed: {$errorMsg}");
        }

        $bank = $result['bank'];
        return redirect()->route('admin.question-banks.preview', $bank)
            ->with('success', "🎉 Successfully imported {$result['imported_count']} question(s) into Question Bank '{$bank->name}'!");
    }

    /**
     * Download specific clean sample CSV template per question type.
     */
    public function downloadSampleCsv(Request $request)
    {
        $typeMap = [
            'multiple_choice' => '1_multiple_choice_sample.csv',
            'count_objects'   => '2_count_objects_sample.csv',
            'matching'        => '3_matching_sample.csv',
            'complete_pattern'=> '4_complete_pattern_sample.csv',
            'pattern'         => '4_complete_pattern_sample.csv',
            'fill_blank'      => '5_fill_blank_sample.csv',
            'true_false'      => '6_true_false_sample.csv',
            'drag_sequence'   => '7_drag_sequence_sample.csv',
            'drag_sort'       => '8_drag_sort_sample.csv',
            'speak_repeat'    => '9_speak_repeat_sample.csv',
        ];

        $type = $request->get('type', 'multiple_choice');
        $fileName = $typeMap[$type] ?? '1_multiple_choice_sample.csv';
        $samplePath = public_path("samples/{$fileName}");

        if (!file_exists($samplePath)) {
            $samplePath = public_path('samples/1_multiple_choice_sample.csv');
        }

        return response()->download($samplePath, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Shared validation rules for store & update.
     */
    private function validateBank(Request $request): array
    {
        return $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'subject_id'    => 'nullable|exists:subjects,id',
            'sub_strand_id' => 'nullable|exists:sub_strands,id',
            'quiz_type_id'  => 'nullable|exists:quiz_types,id',
            'difficulty'    => 'nullable|in:easy,medium,hard',
            'status'        => 'nullable|in:draft,published,archived',
            'sort_order'    => 'nullable|integer',
        ]);
    }
}