<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Media;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\QuizQuestion;
use App\Models\QuizType;
use App\Models\SubStrand;

class QuestionBankCsvImporter
{
    /**
     * Import a CSV file into a QuestionBank.
     *
     * @param string $filePath Absolute path to CSV file
     * @param array $meta Metadata for creating QuestionBank (name, subject_id, sub_strand_id, difficulty, status, created_by)
     * @return array Result summary ['success' => bool, 'bank' => QuestionBank, 'imported_count' => int, 'errors' => array]
     */
    public function import(string $filePath, array $meta): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [
                'success' => false,
                'message' => 'CSV file does not exist or is not readable.',
                'imported_count' => 0,
                'errors' => ['File unreadable'],
            ];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return [
                'success' => false,
                'message' => 'Failed to open CSV file.',
                'imported_count' => 0,
                'errors' => ['File open failed'],
            ];
        }

        // Handle UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Extract header
        $rawHeader = fgetcsv($handle, 4096, ',');
        if (!$rawHeader) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'CSV file is empty or invalid.',
                'imported_count' => 0,
                'errors' => ['Empty header'],
            ];
        }

        // Normalize header keys
        $header = array_map(function ($col) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', str_replace('-', '_', $col))));
        }, $rawHeader);

        // Pre-fetch all quiz types for fast lookup
        $quizTypes = QuizType::all()->keyBy(function ($qt) {
            return str_replace('-', '_', strtolower($qt->slug));
        });

        // Pre-fetch media map by file_name and name
        $mediaMap = Media::all()->keyBy(function ($m) {
            return strtolower($m->file_name ?: $m->name);
        });

        // Create OR Fetch Existing QuestionBank Record
        if (!empty($meta['question_bank_id'])) {
            $bank = QuestionBank::find($meta['question_bank_id']);
        }

        if (empty($bank)) {
            $bankName = $meta['name'] ?? 'Imported Question Bank ' . date('Y-m-d H:i');
            $bank = QuestionBank::create([
                'name' => $bankName,
                'subject_id' => $meta['subject_id'] ?? null,
                'sub_strand_id' => $meta['sub_strand_id'] ?? null,
                'description' => $meta['description'] ?? 'Imported via clean CSV template.',
                'difficulty' => $meta['difficulty'] ?? 'medium',
                'status' => $meta['status'] ?? 'published',
                'created_by' => $meta['created_by'] ?? null,
            ]);
        }

        $startSortOrder = max($bank->questions()->max('sort_order') ?? 0, $bank->assignedQuestions()->max('question_bank_questions.sort_order') ?? 0);
        $importedCount = 0;
        $errors = [];
        $lineNum = 1;

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $lineNum++;
            if (empty(array_filter($row))) {
                continue; // Skip empty lines
            }

            // Combine header with row values
            $data = [];
            foreach ($header as $index => $key) {
                $data[$key] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            try {
                // Detect question type from row or fallback
                $qTypeSlug = $data['question_type'] ?? $this->detectTypeFromData($data);
                $qTypeSlug = str_replace('-', '_', strtolower($qTypeSlug));

                // Map legacy/alias slugs
                if ($qTypeSlug === 'tap_answer' || $qTypeSlug === 'listen_choose') {
                    $qTypeSlug = 'multiple_choice';
                } elseif ($qTypeSlug === 'pattern') {
                    $qTypeSlug = 'complete_pattern';
                }

                $quizType = $quizTypes->get($qTypeSlug) ?? $quizTypes->first();
                $prompt = $data['prompt'] ?? 'Question ' . $lineNum;

                // Handle Lesson Title lookup if provided in row
                if (!empty($data['lesson_title']) && !$bank->sub_strand_id) {
                    $lesson = Lesson::where('title', 'like', '%' . $data['lesson_title'] . '%')->first();
                    if ($lesson && $lesson->topic && $lesson->topic->subStrand) {
                        $bank->update(['sub_strand_id' => $lesson->topic->subStrand->id]);
                    }
                }

                // Resolve Prompt Image & Audio
                $promptImgRaw = $data['prompt_image'] ?? $data['pattern_landscape_image'] ?? '';
                $promptImg = $this->resolveMediaUrl($promptImgRaw, $mediaMap);
                $promptAudio = $this->resolveMediaUrl($data['prompt_audio'] ?? '', $mediaMap);

                $hint = $data['hint'] ?? null;
                $explanation = $data['explanation'] ?? null;
                $points = !empty($data['points']) ? (int) $data['points'] : 10;

                // Build Question
                $question = QuizQuestion::create([
                    'question_bank_id' => $bank->id,
                    'quiz_type_id' => $quizType ? $quizType->id : null,
                    'prompt' => $prompt,
                    'prompt_image_url' => $promptImg,
                    'prompt_audio_url' => $promptAudio,
                    'narration_text' => $prompt,
                    'points' => $points,
                    'hint' => $hint,
                    'explanation' => $explanation,
                    'difficulty' => $meta['difficulty'] ?? 'medium',
                    'sort_order' => $startSortOrder + $importedCount + 1,
                    'scoring_config' => $this->buildScoringConfig($qTypeSlug, $data, $mediaMap),
                    'metadata' => [
                        'imported_from_csv' => true,
                        'lesson_title' => $data['lesson_title'] ?? null,
                        'line_number' => $lineNum,
                        'raw_type' => $qTypeSlug,
                    ],
                ]);

                // Also attach to Pivot Table (question_bank_questions)
                $bank->assignedQuestions()->attach($question->id, ['sort_order' => $startSortOrder + $importedCount + 1]);

                // Create Question Options depending on Type
                $this->createQuestionOptions($question, $qTypeSlug, $data, $mediaMap);

                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Line {$lineNum}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'success' => true,
            'bank' => $bank,
            'imported_count' => $importedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Auto-detect question type slug based on columns present in CSV.
     */
    private function detectTypeFromData(array $data): string
    {
        if (isset($data['count_emoji_or_image']) || isset($data['target_count'])) {
            return 'count_objects';
        }
        if (isset($data['pair_1_left']) || isset($data['pair_1_right'])) {
            return 'matching';
        }
        if (isset($data['pattern_landscape_image'])) {
            return 'complete_pattern';
        }
        if (isset($data['blank_word'])) {
            return 'fill_blank';
        }
        if (isset($data['correct_sequence']) || isset($data['item_1'])) {
            return 'drag_sequence';
        }
        if (isset($data['category_a']) || isset($data['category_b'])) {
            return 'drag_sort';
        }
        if (isset($data['target_word'])) {
            return 'speak_repeat';
        }
        if (isset($data['correct_answer']) && in_array(strtolower($data['correct_answer']), ['true', 'false'])) {
            return 'true_false';
        }

        return 'multiple_choice';
    }

    /**
     * Resolve media filename or URL to a public URL.
     */
    private function resolveMediaUrl(string $path, $mediaMap): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        // Search media map by lower-cased filename
        $cleanName = strtolower(basename($path));
        if ($mediaMap->has($cleanName)) {
            return $mediaMap->get($cleanName)->url;
        }

        // Check image file extensions
        $ext = strtolower(pathinfo($cleanName, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif'])) {
            if (file_exists(public_path('storage/media/' . $path))) {
                return asset('storage/media/' . $path);
            }
            if (file_exists(public_path('uploads/media/' . $path))) {
                return asset('uploads/media/' . $path);
            }
        }

        return $path;
    }

    /**
     * Check if a string looks like an image filename or URL.
     */
    private function isImageValue(string $val): bool
    {
        if (empty($val)) return false;
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '/')) return true;
        $ext = strtolower(pathinfo($val, PATHINFO_EXTENSION));
        return in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'svg', 'gif']);
    }

    /**
     * Build scoring_config array for specialized question mechanics.
     */
    private function buildScoringConfig(string $type, array $data, $mediaMap): array
    {
        $config = [];

        if ($type === 'count_objects') {
            $rawObj = $data['count_emoji_or_image'] ?? $data['count_emoji'] ?? '🍎';
            if ($this->isImageValue($rawObj)) {
                $config['image_url'] = $this->resolveMediaUrl($rawObj, $mediaMap);
            } else {
                $config['emoji'] = $rawObj;
            }
            $config['target_count'] = !empty($data['target_count']) ? (int) $data['target_count'] : 3;
        } elseif ($type === 'fill_blank') {
            $config['blank_text'] = $data['blank_word'] ?? $data['blank_text'] ?? 'C _ T';
            $config['target_char'] = $data['target_letter'] ?? $data['target_char'] ?? 'A';
        } elseif ($type === 'speak_repeat') {
            $config['target_word'] = $data['target_word'] ?? ($data['prompt'] ?? 'Apple');
        } elseif ($type === 'drag_sort') {
            $config['category_a'] = $data['category_a'] ?? 'Fruits';
            $config['category_b'] = $data['category_b'] ?? 'Animals';
        }

        return $config;
    }

    /**
     * Insert Question Options into database.
     */
    private function createQuestionOptions(QuizQuestion $question, string $type, array $data, $mediaMap): void
    {
        $correctAnswer = strtolower(trim($data['correct_answer'] ?? $data['correct_option'] ?? '1'));

        if ($type === 'true_false') {
            $isTrueCorrect = in_array($correctAnswer, ['1', 'true', 'yes', 't']);
            QuestionOption::create([
                'question_id' => $question->id,
                'text_value' => 'True',
                'is_correct' => $isTrueCorrect,
                'sort_order' => 1,
            ]);
            QuestionOption::create([
                'question_id' => $question->id,
                'text_value' => 'False',
                'is_correct' => !$isTrueCorrect,
                'sort_order' => 2,
            ]);
            return;
        }

        if ($type === 'matching') {
            // Build matching pairs (pair_1_left, pair_1_right, etc.)
            for ($i = 1; $i <= 4; $i++) {
                $leftVal = $data["pair_{$i}_left"] ?? null;
                $rightVal = $data["pair_{$i}_right"] ?? null;

                if ($leftVal || $rightVal) {
                    $leftImg = $this->isImageValue($leftVal ?? '') ? $this->resolveMediaUrl($leftVal, $mediaMap) : null;
                    $leftText = $leftImg ? null : $leftVal;

                    $rightImg = $this->isImageValue($rightVal ?? '') ? $this->resolveMediaUrl($rightVal, $mediaMap) : null;
                    $rightText = $rightImg ? null : $rightVal;

                    // Left Option
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'text_value' => $leftText ?: "Pair {$i} Left",
                        'image_url' => $leftImg,
                        'content_type' => 'left',
                        'match_key' => "pair_{$i}",
                        'is_correct' => true,
                        'sort_order' => ($i * 2) - 1,
                    ]);
                    // Right Option
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'text_value' => $rightText ?: "Pair {$i} Right",
                        'image_url' => $rightImg,
                        'content_type' => 'right',
                        'match_key' => "pair_{$i}",
                        'is_correct' => true,
                        'sort_order' => $i * 2,
                    ]);
                }
            }
            return;
        }

        if ($type === 'drag_sort') {
            $catA = $data['category_a'] ?? 'Category A';
            $catB = $data['category_b'] ?? 'Category B';

            $itemsA = array_filter(array_map('trim', explode(',', $data['cat_a_items'] ?? $data['category_a_items'] ?? '')));
            $itemsB = array_filter(array_map('trim', explode(',', $data['cat_b_items'] ?? $data['category_b_items'] ?? '')));

            $sortOrder = 1;
            foreach ($itemsA as $item) {
                $isImg = $this->isImageValue($item);
                QuestionOption::create([
                    'question_id' => $question->id,
                    'text_value' => $isImg ? null : $item,
                    'image_url' => $isImg ? $this->resolveMediaUrl($item, $mediaMap) : null,
                    'match_key' => $catA,
                    'is_correct' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }
            foreach ($itemsB as $item) {
                $isImg = $this->isImageValue($item);
                QuestionOption::create([
                    'question_id' => $question->id,
                    'text_value' => $isImg ? null : $item,
                    'image_url' => $isImg ? $this->resolveMediaUrl($item, $mediaMap) : null,
                    'match_key' => $catB,
                    'is_correct' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }
            return;
        }

        // Standard 4-Option Layout (multiple_choice, count_objects, complete_pattern, fill_blank, drag_sequence, etc.)
        for ($i = 1; $i <= 4; $i++) {
            $val = $data["option_{$i}"] ?? $data["item_{$i}"] ?? null;

            if ($val !== null && $val !== '') {
                $isImg = $this->isImageValue($val);
                $imgUrl = $isImg ? $this->resolveMediaUrl($val, $mediaMap) : null;
                $textVal = $isImg ? null : $val;

                if ($type === 'drag_sequence') {
                    $isCorrect = true;
                } elseif (in_array($correctAnswer, ['1', '2', '3', '4'])) {
                    $isCorrect = ((int) $correctAnswer === $i);
                } else {
                    $isCorrect = ($correctAnswer === strtolower((string) $val));
                }

                QuestionOption::create([
                    'question_id' => $question->id,
                    'text_value' => $textVal ?: ($isImg ? basename($val) : "Option {$i}"),
                    'image_url' => $imgUrl,
                    'is_correct' => $isCorrect,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
