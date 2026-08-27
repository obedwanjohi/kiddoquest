<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id', 'content_type',
        'text_value', 'image_url', 'audio_url',
        'is_correct', 'match_key', 'sort_order',
    ];

    protected $attributes = [
        'content_type' => 'text',
        'is_correct' => false,
        'sort_order' => 0,
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}