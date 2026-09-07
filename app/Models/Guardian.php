<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Guardian extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'guardian';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'parent_pin',
        'pin_changed_at',
        'is_active',
        'enable_devotional',
        'enable_songs_hub',
    ];

    protected $hidden = [
        'password',
        'parent_pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'parent_pin' => 'hashed',
            'pin_changed_at' => 'datetime',
            'is_active' => 'boolean',
            'enable_devotional' => 'boolean',
            'enable_songs_hub' => 'boolean',
        ];
    }

    /**
     * Check the 4-digit Parent Zone PIN.
     *
     * PINs are stored as bcrypt hashes. A legacy plaintext value (rows created before
     * the hashing migration) is accepted once and upgraded to a hash on success.
     */
    public function verifyPin(string $pin): bool
    {
        $stored = (string) ($this->getRawOriginal('parent_pin') ?? '');

        if ($stored === '') {
            return false;
        }

        if (Hash::isHashed($stored)) {
            return Hash::check($pin, $stored);
        }

        if (hash_equals($stored, $pin)) {
            $this->parent_pin = $pin; // re-saved as a hash by the cast
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * True once the parent has replaced the starter PIN.
     */
    public function hasCustomPin(): bool
    {
        return $this->pin_changed_at !== null;
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }
}