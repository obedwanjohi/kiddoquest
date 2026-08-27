<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChildBadge extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'badge_key',
        'name',
        'icon',
        'awarded_at',
    ];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
        ];
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}