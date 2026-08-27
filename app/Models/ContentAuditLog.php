<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentAuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'entity_type',
        'entity_id',
        'action',
        'from_status',
        'to_status',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo(null, 'entity_type', 'entity_id');
    }

    // ── Helper: log a content action ───────────────────────────

    public static function log(
        string $entityType,
        int $entityId,
        string $action,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?string $notes = null,
        ?array $metadata = null,
    ): self {
        return self::create([
            'admin_id' => auth('admin')->id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'notes' => $notes,
            'metadata' => $metadata,
        ]);
    }

    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created' => '✨',
            'updated' => '✏️',
            'submitted' => '📤',
            'approved', 'published' => '✅',
            'rejected' => '❌',
            'archived' => '📦',
            'deleted' => '🗑️',
            default => '📝',
        };
    }
}