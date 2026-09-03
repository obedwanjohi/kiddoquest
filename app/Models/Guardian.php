<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guardian extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'guardian';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'enable_devotional',
        'enable_songs_hub',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function children()
    {
        return $this->hasMany(Child::class);
    }
}