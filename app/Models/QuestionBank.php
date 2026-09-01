<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class QuestionBank extends Model
{
    protected $fillable = [
        'subject_id', 'sub_strand_id', 'quiz_type_id',
        'name', 'description', 'difficulty',
        'status', 'sort_order',
        'created_by',
    ];

    protected $attributes = [
        'difficulty' => 'medium',
        'status' => 'draft',
        'sort_order' => 0,
    ];

    // Removed lesson method as question banks are standalone

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    public function quizType(): BelongsTo
    {
        return $this->belongsTo(QuizType::class);
    }

    /**
     * Legacy direct link (questions created with question_bank_id).
     * Kept for backward compatibility.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }

    /**
     * Many-to-many assigned questions (normalized design).
     */
    public function assignedQuestions(): BelongsToMany
    {
        return $this->belongsToMany(QuizQuestion::class, 'question_bank_questions', 'question_bank_id', 'question_id')
            ->withPivot('sort_order')
            ->orderBy('question_bank_questions.sort_order')
            ->withTimestamps();
    }

    /**
     * Pull a random subset of questions for a quiz attempt with exclusion filtering
     * and adaptive weak-spot fallback.
     *
     * @param  int   $count       Number of questions to draw
     * @param  bool  $shuffle     Whether to randomize
     * @param  array $excludeIds  Question IDs to exclude (for rotation)
     * @param  array $weakIds     Question IDs where child previously struggled
     */
    public function drawQuestions(int $count, bool $shuffle = true, array $excludeIds = [], array $weakIds = []): Collection
    {
        $hasAssigned = $this->assignedQuestions()->exists();
        $baseQuery = $hasAssigned ? $this->assignedQuestions() : $this->questions();
        $idColumn = $hasAssigned ? 'quiz_questions.id' : 'id';

        // Load all available candidate questions with their quizType and options in 1 query
        $query = clone $baseQuery;
        if (!empty($excludeIds)) {
            $query->whereNotIn($idColumn, $excludeIds);
        }
        $candidates = $query->with(['quizType', 'options'])->get();

        // If candidates are less than requested, fill with remaining pool
        if ($candidates->count() < $count && !empty($excludeIds)) {
            $extra = (clone $baseQuery)->whereNotIn($idColumn, $candidates->pluck('id')->toArray())->with(['quizType', 'options'])->get();
            $candidates = $candidates->concat($extra);
        }

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Group candidates into buckets by quiz_type_id
        $buckets = $candidates->groupBy('quiz_type_id');
        $numBuckets = $buckets->count();

        if ($numBuckets <= 1) {
            // Single question type bank: fallback to standard random draw
            $result = $shuffle ? $candidates->shuffle()->take($count) : $candidates->take($count);
            return $result->values();
        }

        // --- BALANCED BUCKET DRAW LOGIC ---
        $drawn = collect();
        $baseQuota = (int) floor($count / $numBuckets);
        $remainder = $count % $numBuckets;

        // Step 1: Draw base quota per bucket
        foreach ($buckets as $typeId => $bucketQuestions) {
            $pool = $shuffle ? $bucketQuestions->shuffle() : $bucketQuestions;
            $drawCount = min($baseQuota, $pool->count());
            $drawn = $drawn->concat($pool->take($drawCount));
        }

        // Step 2: Fill remainder slots round-robin across available buckets
        if ($drawn->count() < $count) {
            $remainingNeeded = $count - $drawn->count();
            $alreadyDrawnIds = $drawn->pluck('id')->toArray();

            $leftovers = $candidates->whereNotIn('id', $alreadyDrawnIds);
            if ($shuffle) {
                $leftovers = $leftovers->shuffle();
            }

            $drawn = $drawn->concat($leftovers->take($remainingNeeded));
        }

        // Final shuffle so questions from different buckets are intermingled
        return $shuffle ? $drawn->shuffle()->values() : $drawn->values();
    }

    /**
     * Total number of questions in the bank (both pools).
     */
    public function getPoolCountAttribute(): int
    {
        return $this->assignedQuestions()->exists()
            ? $this->assignedQuestions()->count()
            : $this->questions()->count();
    }
}
