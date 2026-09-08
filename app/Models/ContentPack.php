<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * A published, versioned export of one adventure world.
 *
 * Publishing writes a new version rather than overwriting, so a device that is
 * half way through a mission keeps playing the version it downloaded.
 */
class ContentPack extends Model
{
    protected $fillable = [
        'pack_id', 'version', 'world_id', 'subject_id', 'level_code', 'subject_code',
        'world_slug', 'name', 'icon', 'theme_color', 'is_free', 'sort_order',
        'mission_count', 'question_count', 'media_count',
        'bytes_core', 'bytes_video', 'sha256', 'json_path', 'url', 'missing_media', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_free'        => 'boolean',
            'version'        => 'integer',
            'sort_order'     => 'integer',
            'mission_count'  => 'integer',
            'question_count' => 'integer',
            'media_count'    => 'integer',
            'bytes_core'     => 'integer',
            'bytes_video'    => 'integer',
            'missing_media'  => 'array',
            'published_at'   => 'datetime',
        ];
    }

    public function world(): BelongsTo
    {
        return $this->belongsTo(AdventureWorld::class, 'world_id');
    }

    /** The newest published version of every pack, keyed by pack_id. */
    public static function latestVersions(): Collection
    {
        return static::query()
            ->orderBy('pack_id')
            ->orderByDesc('version')
            ->get()
            ->groupBy('pack_id')
            ->map(fn (Collection $group) => $group->first())
            ->values();
    }

    public function toCatalogArray(): array
    {
        return [
            'pack_id'      => $this->pack_id,
            'version'      => $this->version,
            'world_id'     => $this->world_id,
            'world_slug'   => $this->world_slug,
            'name'         => $this->name,
            'icon'         => $this->icon,
            'theme_color'  => $this->theme_color,
            'level'        => $this->level_code,
            'subject_code' => $this->subject_code,
            'is_free'      => $this->is_free,
            'sort_order'   => $this->sort_order,
            'missions'     => $this->mission_count,
            'questions'    => $this->question_count,
            'bytes_core'   => $this->bytes_core,
            'bytes_video'  => $this->bytes_video,
            'sha256'       => $this->sha256,
            'url'          => $this->url,
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
