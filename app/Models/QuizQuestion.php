<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends Model
{
    use SoftDeletes;

    protected $table = 'quiz_questions';

    protected $fillable = [
        'quiz_id', 'question_bank_id', 'quiz_type_id', 'type', 'prompt',
        'prompt_image_url', 'prompt_audio_url', 'narration_id',
        'points', 'sort_order', 'hint', 'explanation',
        'scoring_config', 'metadata',
        'cbc_outcome_code', 'difficulty',
        'narration_text', 'voice_profile',
    ];

    protected $attributes = [
        'points' => 1,
        'sort_order' => 0,
    ];

    protected $casts = [
        'scoring_config' => 'array',
        'metadata' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function narration(): BelongsTo
    {
        return $this->belongsTo(Narration::class);
    }

    public function quizType(): BelongsTo
    {
        return $this->belongsTo(QuizType::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class, 'question_id')->orderBy('sort_order');
    }

    /**
     * Many-to-many: banks this question is assigned to (normalized design).
     */
    public function questionBanks(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBank::class, 'question_bank_questions', 'question_id', 'question_bank_id')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}
