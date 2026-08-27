<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizType extends Model
{
    protected $fillable = [
        'code', 'name', 'slug', 'description', 'icon',
        'interaction_mode', 'has_options', 'has_media_prompt',
        'is_scoring_type', 'sort_order', 'is_active',
    ];

    protected $attributes = [
        'icon' => '❓',
        'interaction_mode' => 'tap',
        'has_options' => true,
        'has_media_prompt' => false,
        'is_scoring_type' => true,
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected $casts = [
        'has_options' => 'boolean',
        'has_media_prompt' => 'boolean',
        'is_scoring_type' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}