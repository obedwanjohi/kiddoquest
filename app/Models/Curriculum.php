<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Curriculum extends Model
{
    use SoftDeletes;

    /**
     * Laravel would pluralize "Curriculum" to "curricula" incorrectly as
     * "curricula" is already the intended table — set it explicitly to be safe.
     */
    protected $table = 'curricula';

    protected $fillable = [
        'name', 'slug', 'code', 'description',
        'color', 'icon', 'sort_order', 'status',
    ];

    protected $attributes = [
        'color' => '#4F46E5',
        'icon' => '🎓',
        'sort_order' => 0,
        'status' => 'draft',
    ];

    protected static function booted(): void
    {
        static::creating(function (Curriculum $curriculum) {
            if (empty($curriculum->slug)) {
                $curriculum->slug = static::uniqueSlug($curriculum->name);
            }
        });

        static::updating(function (Curriculum $curriculum) {
            if ($curriculum->isDirty('name') && empty($curriculum->slug)) {
                $curriculum->slug = static::uniqueSlug($curriculum->name);
            }
        });
    }

    protected static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $count = static::withTrashed()->where('slug', 'LIKE', "{$base}%")->count();

        return $count > 0 ? "{$base}-{$count}" : $base;
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('sort_order');
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }
}
