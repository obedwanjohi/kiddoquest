<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MissionAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'mission_id',
        'score',
        'total',
        'stars',
        'passed',
        'answers',
        'time_spent',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'answers' => 'array',
            'completed_at' => 'datetime',
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

    /**
     * Percentage score (0-100).
     */
    public function percentage(): int
    {
        if ($this->total === 0) {
            return 0;
        }

        return (int) round(($this->score / $this->total) * 100);
    }
}