<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Narration extends Model
{
    protected $fillable = [
        'narratable_type',
        'narratable_id',
        'slot',
        'text',
        'audio_path',
        'language',
        'voice_profile',
        'status',
    ];

    /**
     * Full URL to the audio file (for <audio> playback).
     */
    public function getAudioUrlAttribute(): ?string
    {
        return $this->audio_path ? asset('storage/' . $this->audio_path) : null;
    }

    /**
     * True when we have a real uploaded audio file to play.
     */
    public function getHasAudioAttribute(): bool
    {
        return (bool) $this->audio_path;
    }

    protected $attributes = [
        'language' => 'en',
        'status'   => 'draft',
        'slot'     => 'intro',
    ];

    /**
     * The parent model this narration belongs to (Lesson, QuizQuestion, etc.).
     */
    public function narratable()
    {
        return $this->morphTo();
    }

    /**
     * Scope to a specific language.
     */
    public function scopeForLanguage($query, string $language = 'en')
    {
        return $query->where('language', $language);
    }

    /**
     * Scope to a specific slot (intro, content, summary, etc.).
     */
    public function scopeForSlot($query, string $slot)
    {
        return $query->where('slot', $slot);
    }
}