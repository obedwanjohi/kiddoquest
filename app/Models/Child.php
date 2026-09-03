<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'guardian_id',
        'name',
        'avatar',
        'favorite_color',
        'recommended_level',
        'birthdate',
        'total_stars',
        'star_coins',
        'unlocked_items',
        'equipped_hat',
        'streak_days',
        'last_streak_date',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'total_stars' => 'integer',
            'star_coins' => 'integer',
            'unlocked_items' => 'array',
            'streak_days' => 'integer',
            'last_streak_date' => 'date',
            'last_played_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Avatar System (identifier-based, not emoji-based)
    |--------------------------------------------------------------------------
    |
    | We store identifiers like 'lion', 'elephant' — NOT raw emojis.
    | This lets us swap to illustrated PNG characters later without
    | touching the database or any controller logic.
    |
    */

    public const AVATARS = [
        'lion'       => ['emoji' => '🦁', 'name' => 'Leo the Lion'],
        'elephant'   => ['emoji' => '🐘', 'name' => 'Eli the Elephant'],
        'giraffe'    => ['emoji' => '🦒', 'name' => 'Gigi the Giraffe'],
        'monkey'     => ['emoji' => '🐒', 'name' => 'Milo the Monkey'],
        'tiger'      => ['emoji' => '🐯', 'name' => 'Tara the Tiger'],
        'fox'        => ['emoji' => '🦊', 'name' => 'Finn the Fox'],
        'panda'      => ['emoji' => '🐼', 'name' => 'Pip the Panda'],
        'koala'      => ['emoji' => '🐨', 'name' => 'Koko the Koala'],
        'rabbit'     => ['emoji' => '🐰', 'name' => 'Ruby the Rabbit'],
        'frog'       => ['emoji' => '🐸', 'name' => 'Flick the Frog'],
        'owl'        => ['emoji' => '🦉', 'name' => 'Olive the Owl'],
        'cat'        => ['emoji' => '🐱', 'name' => 'Cleo the Cat'],
        'dog'        => ['emoji' => '🐶', 'name' => 'Dash the Dog'],
        'cow'        => ['emoji' => '🐮', 'name' => 'Clover the Cow'],
        'pig'        => ['emoji' => '🐷', 'name' => 'Penny the Pig'],
        'unicorn'    => ['emoji' => '🦄', 'name' => 'Uma the Unicorn'],
        'dino'       => ['emoji' => '🦖', 'name' => 'Rex the Dino'],
        'robot'      => ['emoji' => '🤖', 'name' => 'Beep the Robot'],
        'dragon'     => ['emoji' => '🐉', 'name' => 'Ignis the Dragon'],
    ];

    /**
     * Get the emoji representation for display.
     */
    public function getAvatarEmojiAttribute(): string
    {
        return self::AVATARS[$this->avatar]['emoji'] ?? '🧒';
    }

    /**
     * Get the emoji representation for equipped hat.
     */
    public function getEquippedHatEmojiAttribute(): ?string
    {
        $hats = [
            'hat_star'       => '🌟',
            'hat_crown'      => '👑',
            'hat_pirate'     => '🏴‍☠️',
            'hat_superhero'  => '🦸',
            'hat_sunglasses' => '🕶️',
            'hat_astronaut'  => '👨‍🚀',
            'hat_dino'       => '🦖',
            'hat_party'      => '🥳',
        ];

        return $hats[$this->equipped_hat] ?? null;
    }

    /**
     * Check if item is in child's unlocked_items inventory.
     */
    public function hasUnlockedItem(string $itemId): bool
    {
        $unlocked = $this->unlocked_items ?? [];
        return in_array($itemId, $unlocked, true);
    }

    /**
     * Relationship to question attempts log.
     */
    public function questionAttempts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChildQuestionAttempt::class);
    }

    /**
     * Get question IDs recently attempted by this child for a mission (Exclusion Filter).
     */
    public function getRecentlyAttemptedQuestionIds(int $missionId, int $days = 7): array
    {
        return $this->questionAttempts()
            ->where('mission_id', $missionId)
            ->where('attempted_at', '>=', now()->subDays($days))
            ->pluck('question_id')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get question IDs where the child struggled / answered incorrectly.
     */
    public function getWeakQuestionIds(?int $questionBankId = null): array
    {
        $query = $this->questionAttempts()->where('is_correct', false);

        if ($questionBankId) {
            $query->where('question_bank_id', $questionBankId);
        }

        return $query->pluck('question_id')->unique()->values()->toArray();
    }

    /**
     * Award star coins to child.
     */
    public function awardCoins(int $amount): void
    {
        $this->increment('star_coins', max(0, $amount));
    }

    /**
     * Get the character name for this avatar.
     */
    public function getAvatarNameAttribute(): string
    {
        return self::AVATARS[$this->avatar]['name'] ?? 'Friend';
    }

    /**
     * Get avatar identifier keys for forms.
     */
    public static function avatarIdentifiers(): array
    {
        return array_keys(self::AVATARS);
    }

    /*
    |--------------------------------------------------------------------------
    | Age & Learning Level
    |--------------------------------------------------------------------------
    */

    /**
     * Calculate age in years from birthdate.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birthdate) {
            return null;
        }

        return $this->birthdate->age;
    }

    /**
     * Get a human-readable age string.
     */
    public function getAgeDisplayAttribute(): string
    {
        if (! $this->birthdate) {
            return '—';
        }

        $years = $this->age;

        if ($years === 0) {
            $months = $this->birthdate->diffInMonths(now());
            return "{$months} months";
        }

        return "{$years} year" . ($years > 1 ? 's' : '');
    }

    /**
     * Recommend a learning level based on age (Kenyan CBC system).
     *
     * PP1 = Play Problem (age 4)
     * PP2 = Pre-Primary 2 (age 5)
     * Grade 1 (age 6)
     * Grade 2 (age 7)
     * Grade 3 (age 8+)
     */
    public static function recommendLevel(?string $birthdate): string
    {
        if (! $birthdate) {
            return 'PP1'; // default
        }

        $age = Carbon::parse($birthdate)->age;

        return match(true) {
            $age <= 3  => 'Play Group',
            $age === 4 => 'PP1',
            $age === 5 => 'PP2',
            $age === 6 => 'Grade 1',
            $age === 7 => 'Grade 2',
            default     => 'Grade 3',
        };
    }

    /**
     * Get level display with description.
     */
    public function getLevelDisplayAttribute(): string
    {
        return $this->recommended_level ?? 'PP1';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function progress()
    {
        return $this->hasMany(ChildProgress::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function badges()
    {
        return $this->hasMany(ChildBadge::class);
    }

    /**
     * Get the progress record for a specific mission.
     */
    public function missionProgress(Mission $mission)
    {
        return $this->progress()->where('mission_id', $mission->id)->first();
    }

    /**
     * Get overall progress percentage (completed lessons / total lessons).
     */
    public function getProgressPercentageAttribute(): int
    {
        $totalMissions = Mission::where('status', 'published')->count();

        if ($totalMissions === 0) {
            return 0;
        }

        $completedMissions = $this->progress()
            ->where('status', 'completed')
            ->count();

        return (int) round(($completedMissions / $totalMissions) * 100);
    }

    /**
     * Check if the child has played before.
     */
    public function getHasPlayedAttribute(): bool
    {
        return $this->last_played_at !== null;
    }

    /**
     * Get a human-readable "last played" string.
     */
    public function getLastPlayedDisplayAttribute(): string
    {
        if (! $this->last_played_at) {
            return 'Never';
        }

        return $this->last_played_at->diffForHumans();
    }

    /**
     * Get total seconds played today by this child.
     */
    public function getTodayPlayedSecondsAttribute(): int
    {
        return (int) MissionAttempt::where('child_id', $this->id)
            ->whereDate('completed_at', now()->toDateString())
            ->sum('time_spent');
    }

    /**
     * Get remaining daily learning time in minutes.
     */
    public function getRemainingTimeMinutesAttribute(): int
    {
        $limit = $this->daily_time_limit_minutes ?? 30;
        if ($limit <= 0) {
            return 999; // Unlimited if 0 or negative
        }

        $playedMinutes = (int) round($this->today_played_seconds / 60);
        return max(0, $limit - $playedMinutes);
    }
}