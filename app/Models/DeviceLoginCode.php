<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A short code shown on a TV so a parent can approve the sign-in from their phone.
 * Ambiguous glyphs (0/O, 1/I) are left out because the code is read off a screen
 * from across a living room.
 */
class DeviceLoginCode extends Model
{
    protected $fillable = [
        'code', 'device_id', 'guardian_id', 'platform', 'approved_at', 'claimed_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'claimed_at'  => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generateCode(int $length = 6): string
    {
        do {
            $code = '';
            $max = strlen(self::ALPHABET) - 1;
            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, $max)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null && $this->guardian_id !== null;
    }
}
