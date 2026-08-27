<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChildProgress extends Model
{
    use HasFactory;

    protected $table = 'child_progress';

    protected $fillable = [
        'child_id',
        'mission_id',
        'status',
        'stars_earned',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'stars_earned' => 'integer',
        ];
    }

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }
}