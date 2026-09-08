<?php

namespace App\Services\Learning;

use App\Models\Child;
use App\Models\ChildBadge;
use App\Models\ChildProgress;
use App\Models\Mission;
use App\Models\MissionAttempt;
use Illuminate\Support\Collection;

/**
 * Awards the badges a child has earned.
 *
 * The `child_badges` table has existed since the beginning and nothing has ever
 * written to it. These are the rules from the plan, evaluated on the server
 * after a mission is recorded, so a badge cannot be granted by a device that
 * simply asks for one.
 *
 * Every rule is idempotent: a badge is awarded once and re-checking is free.
 */
class BadgeService
{
    /**
     * Badge definitions, in the order a child is likely to meet them.
     *
     * @return array<string,array{name:string,icon:string,blurb:string}>
     */
    public static function catalogue(): array
    {
        return [
            'first_mission'    => ['name' => 'First Mission',    'icon' => '🚀', 'blurb' => 'You finished your very first mission'],
            'perfect_mission'  => ['name' => 'Perfect!',          'icon' => '💯', 'blurb' => 'Three stars on a mission'],
            'explorer_10'      => ['name' => 'Explorer',          'icon' => '🧭', 'blurb' => '10 missions passed'],
            'adventurer_25'    => ['name' => 'Adventurer',        'icon' => '🗺️', 'blurb' => '25 missions passed'],
            'master_50'        => ['name' => 'Master Explorer',   'icon' => '🏆', 'blurb' => '50 missions passed'],
            'streak_3'         => ['name' => 'Three Days Running','icon' => '🔥', 'blurb' => 'Played three days in a row'],
            'streak_7'         => ['name' => 'A Whole Week',      'icon' => '⭐', 'blurb' => 'Played seven days in a row'],
            'world_complete'   => ['name' => 'World Complete',    'icon' => '🌍', 'blurb' => 'Finished every mission in a world'],
        ];
    }

    /**
     * Check every rule for this child and award anything newly earned.
     *
     * @return array<int,string> the keys awarded by this call
     */
    public function evaluate(Child $child, ?Mission $mission = null, ?ScoreResult $result = null): array
    {
        $already = ChildBadge::where('child_id', $child->id)->pluck('badge_key')->all();
        $earned = [];

        $passedMissions = ChildProgress::where('child_id', $child->id)
            ->where('status', 'completed')
            ->distinct()
            ->count('mission_id');

        if ($passedMissions >= 1) {
            $earned[] = 'first_mission';
        }

        if ($passedMissions >= 10) {
            $earned[] = 'explorer_10';
        }

        if ($passedMissions >= 25) {
            $earned[] = 'adventurer_25';
        }

        if ($passedMissions >= 50) {
            $earned[] = 'master_50';
        }

        $streak = (int) ($child->streak_days ?? 0);

        if ($streak >= 3) {
            $earned[] = 'streak_3';
        }

        if ($streak >= 7) {
            $earned[] = 'streak_7';
        }

        // Three stars on anything, ever — not only the mission just played, so a
        // child who earned it before badges existed still gets it.
        if (($result?->stars ?? 0) >= 3 || MissionAttempt::where('child_id', $child->id)->where('stars', 3)->exists()) {
            $earned[] = 'perfect_mission';
        }

        if ($mission && $this->hasCompletedWorld($child, $mission)) {
            $earned[] = 'world_complete';
        }

        $newlyEarned = array_values(array_diff(array_unique($earned), $already));

        $this->award($child, $newlyEarned);

        return $newlyEarned;
    }

    /**
     * True when every mission in this mission's world has been passed.
     */
    protected function hasCompletedWorld(Child $child, Mission $mission): bool
    {
        if (! $mission->adventure_world_id) {
            return false;
        }

        $missionIds = Mission::where('adventure_world_id', $mission->adventure_world_id)
            ->pluck('id');

        if ($missionIds->isEmpty()) {
            return false;
        }

        $passed = ChildProgress::where('child_id', $child->id)
            ->whereIn('mission_id', $missionIds)
            ->where('status', 'completed')
            ->distinct()
            ->count('mission_id');

        return $passed >= $missionIds->count();
    }

    /**
     * @param  array<int,string>  $keys
     */
    protected function award(Child $child, array $keys): void
    {
        $catalogue = self::catalogue();

        foreach ($keys as $key) {
            $definition = $catalogue[$key] ?? null;

            if (! $definition) {
                continue;
            }

            ChildBadge::firstOrCreate(
                ['child_id' => $child->id, 'badge_key' => $key],
                [
                    'name'       => $definition['name'],
                    'icon'       => $definition['icon'],
                    'awarded_at' => now(),
                ]
            );
        }
    }

    /**
     * Everything this child has, for the snapshot.
     */
    public function forChild(Child $child): Collection
    {
        return ChildBadge::where('child_id', $child->id)
            ->orderBy('awarded_at')
            ->get()
            ->map(fn (ChildBadge $badge) => [
                'key'        => $badge->badge_key,
                'name'       => $badge->name,
                'icon'       => $badge->icon,
                'blurb'      => self::catalogue()[$badge->badge_key]['blurb'] ?? null,
                'awarded_at' => optional($badge->awarded_at)->toIso8601String(),
            ]);
    }
}
