<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildQuestionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'mission_id',
        'question_bank_id',
        'question_id',
        'is_correct',
        'time_spent_seconds',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'time_spent_seconds' => 'integer',
            'attempted_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
