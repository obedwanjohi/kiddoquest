<?php

namespace App\Services\Learning;

use App\Models\Child;
use App\Models\ChildBadge;
use App\Models\ChildDailyStat;
use App\Models\Guardian;
use App\Models\LearningEvent;
use App\Models\Mission;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Takes a batch of events from a device, stores them once, and turns them into
 * the projections the rest of the product reads.
 *
 * Two properties matter more than anything else here:
 *  - Storing an event twice is harmless. The device owns the id, the insert
 *    ignores conflicts, and a replay is reported back as accepted.
 *  - Nothing is ever thrown away. An event we cannot make sense of is stored
 *    with status "quarantined" and a reason, so support can look at it later.
 */
class SyncService
{
    public function __construct(
        protected MissionScoringService $scoring,
        protected MissionCompletionService $completion,
        protected ChildSnapshotService $snapshots,
    ) {
    }

    /**
     * @param  array<int,array>  $events
     * @return array{accepted:array<int,string>,rejected:array<int,array{id:string,reason:string}>,stored:int}
     */
    public function ingest(Guardian $guardian, string $deviceId, ?Child $child, array $events): array
    {
        $accepted = [];
        $rejected = [];
        $rows = [];
        $now = now();

        $futureLimit = $now->copy()->addHours((int) config('kiddoquest.sync.future_skew_hours', 24));
        $pastLimit = $now->copy()->subDays((int) config('kiddoquest.sync.past_skew_days', 90));

        foreach ($events as $event) {
            if (! is_array($event)) {
                continue;
            }

            $id = (string) ($event['id'] ?? '');

            if ($id === '' || ! Str::isUuid($id)) {
                $rejected[] = ['id' => $id, 'reason' => 'invalid_id'];
                continue;
            }

            $type = (string) ($event['type'] ?? '');
            $status = 'accepted';
            $reason = null;

            if (! in_array($type, LearningEvent::TYPES, true)) {
                $status = 'quarantined';
                $reason = 'unknown_type';
            }

            $clientTs = $this->parseTimestamp($event['client_ts'] ?? null);

            if ($clientTs === null) {
                $status = 'quarantined';
                $reason ??= 'bad_timestamp';
                $clientTs = CarbonImmutable::instance($now);
            } elseif ($clientTs->greaterThan($futureLimit) || $clientTs->lessThan($pastLimit)) {
                $status = 'quarantined';
                $reason ??= 'clock';
            }

            $eventChildId = isset($event['child_id']) ? (int) $event['child_id'] : $child?->id;

            $rows[] = [
                'id'           => $id,
                'guardian_id'  => $guardian->id,
                'child_id'     => $eventChildId,
                'device_id'    => $deviceId,
                'type'         => $type !== '' ? Str::limit($type, 40, '') : 'unknown',
                'payload'      => json_encode($event['payload'] ?? [], JSON_UNESCAPED_UNICODE),
                'client_ts'    => $clientTs,
                'received_at'  => $now,
                'seq'          => (int) ($event['seq'] ?? 0),
                'pack_version' => isset($event['payload']['pack_version']) ? (int) $event['payload']['pack_version'] : null,
                'status'       => $status,
                'reason'       => $reason,
                'processed_at' => null,
            ];

            if ($status === 'accepted') {
                $accepted[] = $id;
            } else {
                // Still stored, still acknowledged: the device must not keep retrying.
                $accepted[] = $id;
                $rejected[] = ['id' => $id, 'reason' => $reason ?? 'quarantined'];
            }
        }

        $stored = 0;

        foreach (array_chunk($rows, 100) as $chunk) {
            $stored += DB::table('learning_events')->insertOrIgnore($chunk);
        }

        return ['accepted' => $accepted, 'rejected' => $rejected, 'stored' => $stored];
    }

    /**
     * Apply every event this child has that has not been applied yet, oldest first.
     *
     * Running per child means two children never contend for the same rows, which
     * is what lets this scale by simply adding workers.
     */
    public function project(Child $child, int $limit = 500): int
    {
        $events = LearningEvent::query()
            ->where('child_id', $child->id)
            ->where('status', 'accepted')
            ->whereNull('processed_at')
            ->orderBy('client_ts')
            ->orderBy('seq')
            ->limit($limit)
            ->get();

        $applied = 0;

        foreach ($events as $event) {
            try {
                $this->applyEvent($child, $event);
            } catch (\Throwable $e) {
                Log::error('Failed to project learning event', [
                    'event_id' => $event->id,
                    'type'     => $event->type,
                    'child_id' => $child->id,
                    'error'    => $e->getMessage(),
                ]);

                $event->status = 'quarantined';
                $event->reason = 'projection_error';
            }

            $event->processed_at = now();
            $event->save();
            $applied++;

            $child->refresh();
        }

        return $applied;
    }

    protected function applyEvent(Child $child, LearningEvent $event): void
    {
        $payload = $event->payload ?? [];

        match ($event->type) {
            'mission_completed' => $this->applyMissionCompleted($child, $event, $payload),
            'mission_abandoned' => $this->applyTime($child, $event, (int) ($payload['seconds'] ?? 0)),
            'session_heartbeat', 'session_ended' => $this->applyTime($child, $event, (int) ($payload['seconds'] ?? 0)),
            'shop_purchased'    => $this->applyPurchase($child, $payload),
            'shop_equipped'     => $this->applyEquip($child, $payload),
            'badge_claimed'     => $this->applyBadge($child, $event, $payload),
            default             => null, // analytics-only events are stored, not projected
        };
    }

    protected function applyMissionCompleted(Child $child, LearningEvent $event, array $payload): void
    {
        $missionId = (int) ($payload['mission_id'] ?? 0);
        $mission = $missionId > 0 ? Mission::find($missionId) : null;

        if (! $mission) {
            $event->status = 'quarantined';
            $event->reason = 'unknown_mission';

            return;
        }

        $answers = is_array($payload['answers'] ?? null) ? $payload['answers'] : [];
        $result = $this->scoring->scoreMission($mission, $answers);

        if ($result->disagreesWith(
            isset($payload['score']) ? (int) $payload['score'] : null,
            isset($payload['stars']) ? (int) $payload['stars'] : null
        )) {
            Log::info('Client score differed from server score', [
                'event_id'      => $event->id,
                'mission_id'    => $mission->id,
                'client_score'  => $payload['score'] ?? null,
                'client_stars'  => $payload['stars'] ?? null,
                'server_score'  => $result->score,
                'server_stars'  => $result->stars,
            ]);
        }

        $this->completion->apply($child, $mission, $result, [
            'event_id'     => $event->id,
            'pack_version' => $event->pack_version,
            'time_spent'   => (int) ($payload['time_spent'] ?? 0),
            'answers'      => $answers,
            'completed_at' => $event->client_ts ?? $event->received_at,
            'source'       => 'app',
        ]);
    }

    /**
     * Screen time is counted from the device's own local day so a session that
     * starts before midnight lands where the family expects it to.
     *
     * Seconds played is the sum of time inside missions (from mission_completed
     * and mission_abandoned) and time outside them (from heartbeats). A client
     * must therefore not send heartbeats while a mission is running, or the same
     * minute would be counted twice. The app today sends no heartbeats at all.
     */
    protected function applyTime(Child $child, LearningEvent $event, int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $seconds = min($seconds, 3600);
        // Midnight of the local day, so this matches the row the mission
        // projection writes rather than inserting a duplicate.
        $day = ($event->client_ts ?? $event->received_at)
            ->copy()
            ->setTimezone(config('kiddoquest.timezone', 'Africa/Nairobi'))
            ->startOfDay();

        $stat = ChildDailyStat::firstOrNew(['child_id' => $child->id, 'day' => $day]);
        $stat->seconds_played = (int) $stat->seconds_played + $seconds;
        $stat->save();
    }

    /**
     * A purchase the child already saw succeed offline is never taken back. If
     * the server ledger cannot cover it, the item is still granted and the
     * balance simply stops at zero.
     */
    protected function applyPurchase(Child $child, array $payload): void
    {
        $itemId = (string) ($payload['item_id'] ?? '');

        if ($itemId === '') {
            return;
        }

        // The price comes from the server's catalogue, never from the device.
        $catalogue = array_merge(
            (array) config('kiddoquest.shop.hats', []),
            (array) config('kiddoquest.shop.characters', [])
        );
        $cost = (int) ($catalogue[$itemId] ?? max(0, min((int) ($payload['cost'] ?? 0), 1000)));
        $unlocked = $child->unlocked_items ?? [];

        if (! in_array($itemId, $unlocked, true)) {
            $unlocked[] = $itemId;
            $child->unlocked_items = array_values($unlocked);
        }

        $child->star_coins = max(0, (int) $child->star_coins - $cost);
        $child->save();
    }

    protected function applyEquip(Child $child, array $payload): void
    {
        $itemId = (string) ($payload['item_id'] ?? '');
        $kind = (string) ($payload['type'] ?? 'hat');

        if ($itemId === '') {
            return;
        }

        if ($kind === 'hat') {
            $child->equipped_hat = $itemId;
        } elseif ($kind === 'character' || $kind === 'avatar') {
            $child->avatar = str_replace('char_', '', $itemId);
        }

        $child->save();
    }

    protected function applyBadge(Child $child, LearningEvent $event, array $payload): void
    {
        $key = (string) ($payload['badge_key'] ?? '');

        if ($key === '') {
            return;
        }

        ChildBadge::firstOrCreate(
            ['child_id' => $child->id, 'badge_key' => $key],
            [
                'name'       => (string) ($payload['name'] ?? Str::headline($key)),
                'icon'       => (string) ($payload['icon'] ?? '🏅'),
                'awarded_at' => $event->client_ts ?? now(),
            ]
        );
    }

    /**
     * A cursor is just the newest event we have for this child. The device sends
     * it back so a second device can ask for everything after it.
     */
    public function cursorFor(Child $child): ?string
    {
        $latest = LearningEvent::where('child_id', $child->id)
            ->orderByDesc('received_at')
            ->orderByDesc('seq')
            ->first(['received_at', 'seq']);

        if (! $latest) {
            return null;
        }

        return $latest->received_at->toIso8601String() . '#' . (int) $latest->seq;
    }

    protected function parseTimestamp(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
