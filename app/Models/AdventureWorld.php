<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class AdventureWorld extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject_id',
        'theme_color',
        'icon',
        'description',
        'sort_order',
        'is_locked',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Resolve route model binding by either ID or slug.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_numeric($value)) {
            return $this->where('id', (int) $value)->first() 
                ?? $this->where('slug', $value)->firstOrFail();
        }

        return $this->where('slug', $value)->firstOrFail();
    }

    protected static function booted(): void
    {
        static::creating(function (AdventureWorld $world) {
            $world->slug ??= Str::slug($world->name);
        });
    }

    /**
     * The missions that belong to this adventure world.
     */
    public function missions()
    {
        return $this->hasMany(Mission::class)->orderBy('sort_order');
    }

    /**
     * Determine subject category (math, english, cre, tracing).
     */
    public function getSubjectCategoryAttribute(): string
    {
        if ($this->subject) {
            $sName = strtolower($this->subject->name . ' ' . $this->subject->slug);
            if (str_contains($sName, 'trace') || str_contains($sName, 'tracing') || str_contains($sName, 'writing')) {
                return 'tracing';
            }
            if (str_contains($sName, 'english') || str_contains($sName, 'language') || str_contains($sName, 'phonic') || str_contains($sName, 'vocab') || str_contains($sName, 'speak')) {
                return 'english';
            }
            if (str_contains($sName, 'cre') || str_contains($sName, 'relig') || str_contains($sName, 'value') || str_contains($sName, 'god') || str_contains($sName, 'moral')) {
                return 'cre';
            }
            return 'math';
        }

        $name = strtolower($this->name . ' ' . $this->slug);
        if (str_contains($name, 'trace') || str_contains($name, 'tracing') || str_contains($name, 'pattern') || str_contains($name, 'line-tracing')) {
            return 'tracing';
        }
        if (str_contains($name, 'speak') || str_contains($name, 'safari') || str_contains($name, 'phonics') || str_contains($name, 'letter') || str_contains($name, 'english') || str_contains($name, 'word') || str_contains($name, 'castle') || str_contains($name, 'treasure')) {
            return 'english';
        }
        if (str_contains($name, 'ocean') || str_contains($name, 'cove') || str_contains($name, 'creation') || str_contains($name, 'value') || str_contains($name, 'cre') || str_contains($name, 'god') || str_contains($name, 'village') || str_contains($name, 'rainbow')) {
            return 'cre';
        }
        return 'math';
    }

    /**
     * Get display subject name with emoji.
     */
    public function getSubjectNameAttribute(): string
    {
        return match($this->subject_category) {
            'tracing' => 'Tracing & Writing ✏️',
            'english' => 'Language & Phonics 📖',
            'cre'     => 'CRE & Moral Values ✝️',
            default   => 'Mathematics 🔢',
        };
    }
}