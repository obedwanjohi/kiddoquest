<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'status',
        'sort_order',
        'created_by',
        'level_id',
    ];

    protected $attributes = [
        'icon' => '📚',
        'color' => '#4F46E5',
        'status' => 'draft',
        'sort_order' => 0,
    ];

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('sort_order');
    }

    public function adventureWorlds(): HasMany
    {
        return $this->hasMany(AdventureWorld::class);
    }

    public function lessons(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Topic::class);
    }

    /**
     * Sub-strands associated with this subject (via topics/strands).
     */
    public function subStrands(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(SubStrand::class, Topic::class, 'subject_id', 'strand_id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (Subject $subject) {
            if (empty($subject->slug)) {
                $subject->slug = $subject->generateUniqueSlug($subject->name);
            }
        });

        static::updating(function (Subject $subject) {
            if ($subject->isDirty('name') && empty($subject->slug)) {
                $subject->slug = $subject->generateUniqueSlug($subject->name);
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