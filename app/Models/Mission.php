<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Mission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lesson_id',
        'adventure_world_id',
        'question_bank_id',
        'thumbnail_media_id',
        'video_media_id',
        'title',
        'display_title',
        'slug',
        'description',
        'intro_narration_text',
        'intro_voice_profile',
        'video_url',
        'allow_replay',
        'outro_narration_text',
        'outro_voice_profile',
        'pass_threshold_percent',
        'stars_reward',
        'questions_per_session',
        'randomize_questions',
        'estimated_minutes',
        'status',
        'sort_order',
        'created_by',
    ];

    protected $attributes = [
        'allow_replay' => true,
        'pass_threshold_percent' => 60,
        'stars_reward' => 3,
        'questions_per_session' => 10,
        'randomize_questions' => true,
        'estimated_minutes' => 5,
        'status' => 'draft',
        'sort_order' => 0,
    ];

    protected $casts = [
        'allow_replay' => 'boolean',
        'randomize_questions' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class);
    }

    public function adventureWorld(): BelongsTo
    {
        return $this->belongsTo(AdventureWorld::class);
    }

    public function thumbnailMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function videoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // ── Boot / Slug ────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Mission $mission) {
            if (empty($mission->slug)) {
                $mission->slug = $mission->generateUniqueSlug($mission->title);
            }
        });

        static::updating(function (Mission $mission) {
            if ($mission->isDirty('title') && empty($mission->slug)) {
                $mission->slug = $mission->generateUniqueSlug($mission->title);
            }
        });
    }

    protected function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = static::withTrashed()
            ->where('slug', 'LIKE', "{$slug}%")
            ->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getDisplayTitleAttribute($value): string
    {
        return $value ?? $this->title;
    }

    // ── Status Helpers ─────────────────────────────────────────

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => '#6b7280',
            'in_review' => '#f59e0b',
            'published' => '#22c55e',
            default => '#6b7280',
        };
    }
}