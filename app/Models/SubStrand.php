<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubStrand extends Model
{
    protected $fillable = [
        'strand_id', 'name', 'slug', 'description',
        'code', 'sort_order', 'status',
    ];

    protected $attributes = [
        'sort_order' => 0,
        'status' => 'published',
    ];

    protected static function booted(): void
    {
        static::creating(function (SubStrand $subStrand) {
            $subStrand->slug ??= Str::slug($subStrand->name);
        });
    }

    /**
     * The Strand (Topic) this sub-strand belongs to.
     */
    public function strand(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'strand_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }
}