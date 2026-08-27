<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\Subject;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::withCount('questions')
            ->with('lesson.topic.subject')
            ->orderByDesc('id')
            ->get();

        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $lessons = Lesson::with('topic.subject')->orderBy('title')->get();
        // Exclude QT-11 (Memory Match), QT-12 (Tracing), QT-13 (Spot & Find) from the builder
        // — Memory Match & Spot & Find are removed from MVP; Tracing is system-seeded only.
        $quizTypes = QuizType::where('is_active', true)
            ->whereNotIn('code', ['QT-11', 'QT-12', 'QT-13'])
            ->orderBy('sort_order')
            ->get();
        $subjects = Subject::orderBy('name')->get(['id', 'name']);
        return view('admin.quizzes.create', compact('lessons', 'quizTypes', 'subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'pass_threshold_percent' => 'nullable|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1|max:10',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'status' => 'required|in:draft,in_review,published',
            'sort_order' => 'nullable|integer|min:0',
            'questions' => 'nullable|array',
            'questions.*.quiz_type_id' => 'required_with:questions|exists:quiz_types,id',
            'questions.*.prompt' => 'required_with:questions|string',
            'questions.*.points' => 'nullable|integer|min:1|max:10',
            'questions.*.hint' => 'nullable|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.prompt_image_url' => 'nullable|string',
            'questions.*.prompt_audio_url' => 'nullable|string',
            'questions.*.narration_text' => 'nullable|string',
            'questions.*.voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'questions.*.difficulty' => 'nullable|string|in:easy,medium,hard',
            'questions.*.cbc_outcome_code' => 'nullable|string|max:50',
            'questions.*.additional_images' => 'nullable|array',
            'questions.*.additional_images.*' => 'nullable|string',
            'questions.*.options' => 'nullable|array',
            'questions.*.options.*.content_type' => 'nullable|in:text,image,audio',
            'questions.*.options.*.text_value' => 'nullable|string',
            'questions.*.options.*.image_url' => 'nullable|string',
            'questions.*.options.*.audio_url' => 'nullable|string',
            'questions.*.options.*.match_key' => 'nullable|string',
            'questions.*.options.*.is_correct' => 'nullable',
            'questions.*.metadata' => 'nullable|string', // JSON string from hidden field
            'questions.*.buckets' => 'nullable|array',
            'questions.*.buckets.*.key' => 'nullable|string',
        ]);

        $validated['created_by'] = auth('admin')->id();
        $validated['shuffle_questions'] = $request->boolean('shuffle_questions', true);
        $validated['shuffle_options'] = $request->boolean('shuffle_options', true);

        $quiz = Quiz::create(collect($validated)->except('questions')->toArray());

        // ── Save questions + nested options in one go ──
        if (!empty($validated['questions'])) {
            $qOrder = 0;
            foreach ($validated['questions'] as $qData) {
                $options = $qData['options'] ?? [];
                $buckets = $qData['buckets'] ?? [];
                $qData = collect($qData)->except(['options', 'buckets'])->toArray();
                $qData['sort_order'] = $qOrder++;

                // Build metadata: merge JSON (hotspots) + buckets array (QT-04 Sort)
                $metadata = null;
                if (!empty($qData['metadata']) && is_string($qData['metadata'])) {
                    $decoded = json_decode($qData['metadata'], true);
                    if (is_array($decoded) && !empty($decoded)) $metadata = $decoded;
                }
                if (empty($metadata['buckets']) && !empty($buckets)) {
                    $cleanBuckets = [];
                    foreach ($buckets as $b) {
                        if (!empty($b['key']) || !empty($b['name'])) {
                            $cleanBuckets[] = [
                                'key' => $b['key'] ?? null,
                                'name' => $b['name'] ?? null,
                                'icon' => $b['icon'] ?? null,
                                'color' => $b['color'] ?? null,
                            ];
                        }
                    }
                    if (!empty($cleanBuckets)) {
                        $metadata = $metadata ?? [];
                        $metadata['buckets'] = $cleanBuckets;
                    }
                }
                $qData['metadata'] = $metadata;

                $question = $quiz->questions()->create($qData);

                if (!empty($options)) {
                    $oOrder = 0;
                    foreach ($options as $oData) {
                        $oData['content_type'] = $oData['content_type'] ?? 'text';
                        $oData['is_correct'] = !empty($oData['is_correct']);
                        $oData['sort_order'] = $oOrder++;
                        $question->options()->create($oData);
                    }

                    // Auto-extract config for Speak & Repeat (QT-07) and Count Objects (QT-09)
                    $correctOption = $question->options()->where('is_correct', true)->first();
                    if ($correctOption) {
                        if ($question->quizType->code === 'QT-07') {
                            $existingMeta = $question->metadata ?? [];
                            $existingMeta['word'] = $correctOption->text_value;
                            $question->metadata = $existingMeta;
                            $question->save();
                        } elseif ($question->quizType->code === 'QT-09') {
                            $question->scoring_config = [
                                'count' => (int) $correctOption->text_value,
                                'image_url' => $question->prompt_image_url
                            ];
                            $question->save();
                        }
                    }
                }
            }
        }

        $qCount = $quiz->questions()->count();

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', "Quiz \"{$validated['title']}\" created with {$qCount} question(s)!");
    }

    public function show(Quiz $quiz)
    {
        $quiz->load(['questions.options', 'questions.quizType', 'lesson.topic.subject']);
        // Exclude QT-11 (Memory Match), QT-12 (Tracing), QT-13 (Spot & Find) from the builder
        $quizTypes = QuizType::where('is_active', true)
            ->whereNotIn('code', ['QT-11', 'QT-12', 'QT-13'])
            ->orderBy('sort_order')
            ->get();

        return view('admin.quizzes.show', compact('quiz', 'quizTypes'));
    }

    public function edit(Quiz $quiz)
    {
        $lessons = Lesson::with('topic.subject')->orderBy('title')->get();
        return view('admin.quizzes.edit', compact('quiz', 'lessons'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string',
            'pass_threshold_percent' => 'nullable|integer|min:0|max:100',
            'max_attempts' => 'nullable|integer|min:1|max:10',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'status' => 'required|in:draft,in_review,published',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['shuffle_questions'] = $request->boolean('shuffle_questions', true);
        $validated['shuffle_options'] = $request->boolean('shuffle_options', true);

        $quiz->update($validated);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Quiz settings updated!');
    }

    public function destroy(Quiz $quiz)
    {
        $title = $quiz->title;
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')
            ->with('success', "Quiz \"{$title}\" deleted.");
    }

    // ── Question Builder ──────────────────────────────────────────

    public function addQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'quiz_type_id' => 'required|exists:quiz_types,id',
            'prompt' => 'required|string',
            'prompt_image_url' => 'nullable|string',
            'prompt_audio_url' => 'nullable|string',
            'points' => 'nullable|integer|min:1|max:10',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'narration_text' => 'nullable|string',
            'voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
            'cbc_outcome_code' => 'nullable|string|max:50',
            'additional_images' => 'nullable',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? $quiz->questions()->count();

        // Store additional images in metadata (accepts array or JSON string)
        $additionalImages = $this->parseImageInput($validated['additional_images'] ?? null);
        if (!empty($additionalImages)) {
            $validated['metadata'] = ['additional_images' => $additionalImages];
        }
        unset($validated['additional_images']);

        $quiz->questions()->create($validated);

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question added!');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'prompt_image_url' => 'nullable|string',
            'prompt_audio_url' => 'nullable|string',
            'points' => 'nullable|integer|min:1|max:10',
            'hint' => 'nullable|string',
            'explanation' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'narration_text' => 'nullable|string',
            'voice_profile' => 'nullable|string|in:leo,lily,max,mia,teacher,custom',
            'difficulty' => 'nullable|string|in:easy,medium,hard',
            'cbc_outcome_code' => 'nullable|string|max:50',
            'additional_images' => 'nullable',
        ]);

        // Store additional images in metadata, preserving existing metadata keys
        $existingMeta = $question->metadata ?? [];
        $additionalImages = $this->parseImageInput($validated['additional_images'] ?? null);
        if (!empty($additionalImages)) {
            $existingMeta['additional_images'] = $additionalImages;
        } else {
            unset($existingMeta['additional_images']);
        }
        $validated['metadata'] = !empty($existingMeta) ? $existingMeta : null;
        unset($validated['additional_images']);

        // Keep narration_text if sent so it can override TTS for fill in the blanks
        // unset($validated['narration_text']);

        $question->update($validated);

        // Auto-update QT-09 config if image changed
        if ($question->quizType->code === 'QT-09') {
            $correctOption = $question->options()->where('is_correct', true)->first();
            if ($correctOption) {
                $question->scoring_config = [
                    'count' => (int) $correctOption->text_value,
                    'image_url' => $question->prompt_image_url
                ];
                $question->save();
            }
        }

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question updated!');
    }

    public function duplicateQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $newQuestion = $question->replicate();
        $newQuestion->prompt = $question->prompt . ' (Copy)';
        $newQuestion->sort_order = $quiz->questions()->count();
        $newQuestion->save();

        foreach ($question->options as $option) {
            $newOption = $option->replicate();
            $newOption->question_id = $newQuestion->id;
            $newOption->save();
        }

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question duplicated with all options!');
    }

    public function deleteQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Question deleted.');
    }

    // ── Options Builder ───────────────────────────────────────────

    public function addOption(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'content_type' => 'required|in:text,image,audio,mixed',
            'text_value' => 'nullable|string',
            'image_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'is_correct' => 'boolean',
            'match_key' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_correct'] = $request->boolean('is_correct', false);
        $validated['sort_order'] = $validated['sort_order'] ?? $question->options()->count();

        // Auto-detect content_type as 'mixed' if multiple media present, otherwise set based on what's filled
        $hasText = !empty($validated['text_value']);
        $hasImage = !empty($validated['image_url']);
        $hasAudio = !empty($validated['audio_url']);

        if ($validated['content_type'] === 'mixed' || ($hasText + $hasImage + $hasAudio) > 1) {
            $validated['content_type'] = 'mixed';
        } elseif ($hasImage && !$hasText && !$hasAudio) {
            $validated['content_type'] = 'image';
        } elseif ($hasAudio && !$hasText && !$hasImage) {
            $validated['content_type'] = 'audio';
        } else {
            $validated['content_type'] = 'text';
        }

        $question->options()->create($validated);

        // Auto-update parent question config if this is the correct option
        if ($validated['is_correct']) {
            if ($question->quizType->code === 'QT-07') {
                $existingMeta = $question->metadata ?? [];
                $existingMeta['word'] = $validated['text_value'];
                $question->metadata = $existingMeta;
                $question->save();
            } elseif ($question->quizType->code === 'QT-09') {
                $question->scoring_config = [
                    'count' => (int) $validated['text_value'],
                    'image_url' => $question->prompt_image_url
                ];
                $question->save();
            }
        }

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Option added!');
    }

    public function deleteOption(Quiz $quiz, QuizQuestion $question, QuestionOption $option)
    {
        $option->delete();

        return redirect()->route('admin.quizzes.show', $quiz)
            ->with('success', 'Option deleted.');
    }

    /**
     * Parse image input that may be an array, JSON string, or null.
     * Returns a clean array of non-empty URL strings.
     */
    private function parseImageInput($input): array
    {
        if (empty($input)) return [];
        if (is_array($input)) {
            return array_values(array_filter($input, fn($url) => !empty($url)));
        }
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return array_values(array_filter($decoded, fn($url) => !empty($url)));
            }
            // Single URL as string
            if (trim($input)) return [trim($input)];
        }
        return [];
    }
}
