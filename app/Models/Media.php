<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'name',
        'disk',
        'file_path',
        'file_name',
        'mime_type',
        'extension',
        'type',
        'size_bytes',
        'thumbnail_path',
        'duration_seconds',
        'width',
        'height',
        'subject_id',
        'tags',
        'alt_text',
        'description',
    ];

    protected $casts = [
        'tags' => 'array',
        'size_bytes' => 'integer',
        'duration_seconds' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'uploaded_by');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }

    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'image' => '🖼️',
            'video' => '🎬',
            'audio' => '🔊',
            'document' => '📄',
            default => '📎',
        };
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration_seconds) return null;
        $mins = floor($this->duration_seconds / 60);
        $secs = $this->duration_seconds % 60;
        return $mins > 0 ? "{$mins}m {$secs}s" : "{$secs}s";
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }
}