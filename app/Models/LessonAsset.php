<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAsset extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_WORKSHEET = 'worksheet';
    public const TYPE_THUMBNAIL = 'thumbnail';
    public const TYPE_BACKGROUND_MUSIC = 'background_music';
    public const TYPE_CELEBRATION_AUDIO = 'celebration_audio';
    public const TYPE_INTRO_AUDIO = 'intro_audio';
    public const TYPE_SUMMARY_AUDIO = 'summary_audio';

    protected $fillable = [
        'lesson_id', 'type', 'title', 'caption', 'media_id', 'display_order', 'status',
    ];

    protected $attributes = [
        'display_order' => 0,
        'status'        => 'draft',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    /**
     * Get all valid asset types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
            self::TYPE_AUDIO,
            self::TYPE_WORKSHEET,
            self::TYPE_THUMBNAIL,
            self::TYPE_BACKGROUND_MUSIC,
            self::TYPE_CELEBRATION_AUDIO,
            self::TYPE_INTRO_AUDIO,
            self::TYPE_SUMMARY_AUDIO,
        ];
    }
}