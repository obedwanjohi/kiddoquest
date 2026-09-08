<?php

namespace App\Services\Learning;

use App\Models\Child;
use App\Models\ChildDailyStat;
use App\Models\ChildProgress;
use App\Models\ContentPack;
use App\Models\Guardian;
use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;

/**
 * The server's authoritative view of a child, which every sync response carries
 * back so a device can correct whatever it computed while it was offline.
 */
class ChildSnapshotService
{
    public function for(Child $child): array
    {
        return [
            'child'            => $this->child($child),
            'progress'         => $this->progress($child),
            'badges'           => app(BadgeService::class)->forChild($child),
            'entitlement'      => $this->entitlement($child->guardian),
            'content_versions' => $this->contentVersions(),
            'server_time'      => now()->toIso8601String(),
        ];
    }

    protected function child(Child $child): array
    {
        return [
            'id'                       => (int) $child->id,
            'name'                     => $child->name,
            'avatar'                   => $child->avatar,
            'level'                    => $child->recommended_level,
            'favorite_color'           => $child->favorite_color,
            'total_stars'              => (int) $child->total_stars,
            'star_coins'               => (int) $child->star_coins,
            'streak_days'              => (int) ($child->streak_days ?? 0),
            'last_streak_date'         => optional($child->last_streak_date)->toDateString(),
            'unlocked_items'           => $child->unlocked_items ?? [],
            'equipped_hat'             => $child->equipped_hat,
            'daily_time_limit_minutes' => (int) ($child->daily_time_limit_minutes ?? 0),
            'today_played_seconds'     => $this->todayPlayedSeconds($child),
            'assigned_mission_id'      => $child->assigned_mission_id ? (int) $child->assigned_mission_id : null,
            'last_played_at'           => optional($child->last_played_at)->toIso8601String(),
        ];
    }

    /**
     * @return array<int,array{mission_id:int,status:string,stars_earned:int,completed_at:?string}>
     */
    protected function progress(Child $child): array
    {
        return ChildProgress::where('child_id', $child->id)
            ->orderBy('mission_id')
            ->get(['mission_id', 'status', 'stars_earned', 'completed_at'])
            ->map(fn (ChildProgress $row) => [
                'mission_id'   => (int) $row->mission_id,
                'status'       => $row->status ?? 'in_progress',
                'stars_earned' => (int) ($row->stars_earned ?? 0),
                'completed_at' => optional($row->completed_at)->toIso8601String(),
            ])
            ->all();
    }

    /**
     * What the family is allowed to play, including the window during which paid
     * worlds keep working after the subscription lapses while they are offline.
     */
    public function entitlement(?Guardian $guardian): array
    {
        $grace = (int) config('kiddoquest.entitlement.offline_grace_days', 7);

        if (! $guardian) {
            return ['plan' => null, 'status' => 'none', 'expires_at' => null, 'offline_grace_days' => $grace, 'enforced' => (bool) config('plans.enforce_subscription', false)];
        }

        $subscription = Subscription::where('guardian_id', $guardian->id)
            ->where('status', 'active')
            ->orderByDesc('expires_at')
            ->first();

        return [
            'plan'               => $subscription->plan_type ?? null,
            'status'             => $subscription && $subscription->expires_at?->isFuture() ? 'active' : ($subscription ? 'expired' : 'none'),
            'expires_at'         => optional($subscription?->expires_at)->toIso8601String(),
            'offline_grace_days' => (int) ($subscription->grace_days ?? $grace),
            'enforced'           => (bool) config('plans.enforce_subscription', false),
        ];
    }

    /**
     * pack_id => newest published version, so the app knows what to re-download.
     */
    public function contentVersions(): array
    {
        return Cache::remember('content:versions', now()->addMinutes(5), function () {
            $versions = [];

            foreach (ContentPack::latestVersions() as $pack) {
                $versions[$pack->pack_id] = (int) $pack->version;
            }

            return $versions;
        });
    }

    protected function todayPlayedSeconds(Child $child): int
    {
        $today = now()->setTimezone(config('kiddoquest.timezone', 'Africa/Nairobi'))->toDateString();

        $stat = ChildDailyStat::where('child_id', $child->id)->whereDate('day', $today)->first();

        if ($stat) {
            return (int) $stat->seconds_played;
        }

        // Older data predates the daily projection; fall back to the attempts table.
        return (int) $child->today_played_seconds;
    }
}
