<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voice extends Model
{
    protected $fillable = [
        'name', 'provider', 'voice_id', 'language',
        'gender', 'description', 'status', 'sort_order',
    ];

    protected $attributes = [
        'provider' => 'browser',
        'language' => 'en',
        'status' => 'active',
        'sort_order' => 0,
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'narration_voice_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
