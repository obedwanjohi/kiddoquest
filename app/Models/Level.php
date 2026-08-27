<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Level extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'curriculum_id',
        'name', 'slug', 'code', 'description',
        'stage', 'min_age', 'max_age',
        'color', 'icon',
        'sort_order', 'status',
    ];

    protected $attributes = [
        'color' => '#4F46E5',
        'icon' => '⭐',
        'sort_order' => 0,
        'status' => 'draft',
    ];

    protected static function booted(): void
    {
        static::creating(function (Level $level) {
            if (empty($level->slug)) {
                $level->slug = static::uniqueSlug($level->name);
            }
        });

        static::updating(function (Level $level) {
            if ($level->isDirty('name') && empty($level->slug)) {
                $level->slug = static::uniqueSlug($level->name);
            }
        });
    }

    protected static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $count = static::withTrashed()->where('slug', 'LIKE', "{$base}%")->count();

        return $count > 0 ? "{$base}-{$count}" : $base;
    }

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('sort_order');
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
}
