<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lesson_id', 'title', 'instructions',
        'pass_threshold_percent', 'max_attempts',
        'shuffle_questions', 'shuffle_options',
        'status', 'sort_order', 'created_by',
    ];

    protected $attributes = [
        'pass_threshold_percent' => 70,
        'max_attempts' => 3,
        'shuffle_questions' => true,
        'shuffle_options' => true,
        'status' => 'draft',
        'sort_order' => 0,
    ];

    protected $casts = [
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('sort_order');
    }
}