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
     * Determine subject category (math, english, cre).
     */
    public function getSubjectCategoryAttribute(): string
    {
        $name = strtolower($this->name);
        if (str_contains($name, 'phonics') || str_contains($name, 'letter') || str_contains($name, 'english') || str_contains($name, 'word')) {
            return 'english';
        }
        if (str_contains($name, 'creation') || str_contains($name, 'value') || str_contains($name, 'cre') || str_contains($name, 'god')) {
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
            'english' => 'English & Phonics 📖',
            'cre'     => 'CRE & Values ✝️',
            default   => 'Mathematics 🔢',
        };
    }
}