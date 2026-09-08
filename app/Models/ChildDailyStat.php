<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per child per local day. This is what the parent dashboard reads
 * instead of aggregating mission_attempts on every page load.
 */
class ChildDailyStat extends Model
{
    protected $fillable = [
        'child_id', 'day', 'seconds_played', 'missions_completed', 'missions_passed',
        'questions', 'correct', 'stars_earned', 'coins_earned',
    ];

    protected function casts(): array
    {
        return [
            'day'                => 'date',
            'seconds_played'     => 'integer',
            'missions_completed' => 'integer',
            'missions_passed'    => 'integer',
            'questions'          => 'integer',
            'correct'            => 'integer',
            'stars_earned'       => 'integer',
            'coins_earned'       => 'integer',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }
}
