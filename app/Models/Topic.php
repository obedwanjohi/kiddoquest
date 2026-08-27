<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subject_id',
        'name',
        'slug',
        'description',
        'icon',
        'status',
        'sort_order',
        'created_by',
    ];

    protected $attributes = [
        'icon' => '📂',
        'status' => 'draft',
        'sort_order' => 0,
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    /**
     * Sub-strands belonging to this strand (Topic).
     */
    public function subStrands(): HasMany
    {
        return $this->hasMany(SubStrand::class, 'strand_id')->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (Topic $topic) {
            if (empty($topic->slug)) {
                $topic->slug = $topic->generateUniqueSlug($topic->name);
            }
        });

        static::updating(function (Topic $topic) {
            if ($topic->isDirty('name') && empty($topic->slug)) {
                $topic->slug = $topic->generateUniqueSlug($topic->name);
            }
        });
    }

    protected function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $count = static::withTrashed()
            ->where('slug', 'LIKE', "{$slug}%")
            ->count();

        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}