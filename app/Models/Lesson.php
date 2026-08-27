<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'topic_id',
        'sub_strand_id',
        'title',
        'slug',
        'summary',
        'learning_objective',
        'intro_narration_text',
        'summary_narration_text',
        'narration_voice_id',
        'content',
        'content_type',
        'video_url',
        'video_path',
        'video_duration_seconds',
        'thumbnail_media_id',
        'video_media_id',
        'intro_narration_id',
        'summary_narration_id',
        'cbc_outcome_code',
        'estimated_minutes',
        'duration_minutes',
        'status',
        'sort_order',
        'created_by',
        'reviewed_by',
        'published_at',
        'submitted_at',
        'reviewed_at',
        'archived_at',
        'version',
        'review_notes',
        'rejection_reason',
    ];

    protected $attributes = [
        'content_type' => 'text',
        'duration_minutes' => 5,
        'status' => 'draft',
        'sort_order' => 0,
        'version' => 1,
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function subStrand(): BelongsTo
    {
        return $this->belongsTo(SubStrand::class);
    }

    /**
     * The AI narration voice selected for this lesson (Module 4).
     */
    public function voice(): BelongsTo
    {
        return $this->belongsTo(Voice::class, 'narration_voice_id');
    }

    public function thumbnailMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
    }

    public function videoMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'video_media_id');
    }

    public function introNarration(): BelongsTo
    {
        return $this->belongsTo(Narration::class, 'intro_narration_id');
    }

    public function summaryNarration(): BelongsTo
    {
        return $this->belongsTo(Narration::class, 'summary_narration_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(LessonAsset::class)->orderBy('display_order');
    }

    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class)->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ContentAuditLog::class, 'entity_id')
            ->where('entity_type', 'Lesson')
            ->orderByDesc('created_at');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->orderBy('sort_order');
    }

    public function missions(): HasMany
    {
        return $this->hasMany(Mission::class)->orderBy('sort_order');
    }

    // ── Boot / Slug ────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson) {
            if (empty($lesson->slug)) {
                $lesson->slug = $lesson->generateUniqueSlug($lesson->title);
            }
        });

        static::updating(function (Lesson $lesson) {
            if ($lesson->isDirty('title') && empty($lesson->slug)) {
                $lesson->slug = $lesson->generateUniqueSlug($lesson->title);
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

    // ── Status Helpers ─────────────────────────────────────────

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->status === 'draft';
    }

    public function getIsInReviewAttribute(): bool
    {
        return $this->status === 'in_review';
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->status === 'archived';
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => '#6b7280',
            'in_review' => '#f59e0b',
            'published' => '#22c55e',
            'archived' => '#94a3b8',
            default => '#6b7280',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}