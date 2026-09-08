<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One thing a child did, exactly as their device recorded it.
 *
 * The device generates the UUID, so re-sending an event is a no-op: the insert
 * uses "do nothing on conflict" and the projection only runs for rows it has
 * not processed yet.
 */
class LearningEvent extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    public const UPDATED_AT = null;

    public const CREATED_AT = null;

    protected $fillable = [
        'id', 'guardian_id', 'child_id', 'device_id', 'type', 'payload',
        'client_ts', 'received_at', 'seq', 'pack_version', 'status', 'reason', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'client_ts'    => 'datetime',
            'received_at'  => 'datetime',
            'processed_at' => 'datetime',
            'seq'          => 'integer',
            'pack_version' => 'integer',
        ];
    }

    /** Event types the API accepts. Anything else is quarantined as unknown_type. */
    public const TYPES = [
        'session_started', 'session_heartbeat', 'session_ended',
        'mission_started', 'question_answered', 'mission_completed', 'mission_abandoned',
        'video_watched', 'shop_purchased', 'shop_equipped',
        'devotional_viewed', 'songs_opened', 'badge_claimed',
    ];

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }
}
