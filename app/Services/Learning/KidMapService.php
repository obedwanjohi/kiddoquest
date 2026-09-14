<?php

namespace App\Services\Learning;

use App\Models\AdventureWorld;
use App\Models\Child;
use App\Models\ContentPack;
use App\Models\MissionAttempt;
use Illuminate\Support\Collection;

/**
 * What a child's adventure map shows.
 *
 * This is the website's own rule, moved here unchanged from KidController so
 * the website and the app ask the same question and get the same answer. The
 * app used to build its map from the content catalogue instead, which picked a
 * different set of worlds for the same child (a PP1 child lost Speak & Repeat
 * Safari and gained worlds the website never showed) and listed no missions at
 * all until a world had been downloaded.
 */
class KidMapService
{
    /**
     * Worlds are matched to the child's level, and a handful of cross-level
     * worlds (tracing, speak and repeat, the QA lab) are always shown.
     */
    public function worldsFor(Child $child): Collection
    {
        $rawLevel = strtolower(str_replace(['_', '-'], ' ', $child->recommended_level ?? ''));

        $worldsQuery = AdventureWorld::with([
            'subject.level',
            'missions' => function ($q) {
                $q->where('status', 'published')->orderBy('sort_order');
            },
        ])->orderBy('sort_order');

        if (! $rawLevel) {
            return $worldsQuery->get();
        }

        return $worldsQuery->where(function ($query) use ($rawLevel) {
            $query->whereHas('subject.level', function ($q) use ($rawLevel) {
                if (str_contains($rawLevel, 'play') || str_contains($rawLevel, 'pg')) {
                    $q->where('code', 'PG')
                        ->orWhere('name', 'like', '%play%');
                } elseif (str_contains($rawLevel, 'pp1')) {
                    $q->where('code', 'PP1')
                        ->orWhere('name', 'like', '%pp1%');
                } elseif (str_contains($rawLevel, 'pp2')) {
                    $q->where('code', 'PP2')
                        ->orWhere('name', 'like', '%pp2%');
                } else {
                    $q->where('code', strtoupper($rawLevel))
                        ->orWhere('name', 'like', "%{$rawLevel}%");
                }
            });

            // Always unlock Tracing Worlds, Speak Repeat Safari & Master QA Lab for all levels
            $query->orWhereIn('slug', [
                'line-tracing-trail', 'letter-tracing-safari', 'number-tracing-kingdom', 'speak-repeat-safari', 'master-qa-lab-world',
            ]);

            // Match Playgroup world slugs directly for Play Group profiles
            if (str_contains($rawLevel, 'play') || str_contains($rawLevel, 'pg')) {
                $query->orWhereIn('slug', [
                    'whispering-forest', 'sunny-meadow', 'cookie-trail',
                    'safari-plains', 'castle-of-discovery',
                    'ocean-cove', 'ocean-cove-creation', 'kindness-village', 'rainbow-mountain', 'rainbow-mountain-values',
                    'creation-realm', 'jesus-realm', 'christian-values-realm',
                    'speak-repeat-safari',
                ]);
            }
        })->get();
    }

    /**
     * The child's progress, keyed by mission.
     *
     * @return array{status: array<int,string>, stars: array<int,int>}
     */
    public function progressFor(Child $child): array
    {
        $records = $child->progress()->get();

        return [
            'status' => $records->pluck('status', 'mission_id')->toArray(),
            'stars'  => $records->pluck('stars_earned', 'mission_id')->toArray(),
        ];
    }

    /**
     * The songs tab opens once a mission has been passed today, or once the
     * day's screen time has run out.
     */
    public function songsUnlocked(Child $child): bool
    {
        $completedToday = MissionAttempt::where('child_id', $child->id)
            ->whereDate('completed_at', now()->toDateString())
            ->where('passed', true)
            ->count();

        return $completedToday >= 1 || $child->remaining_time_minutes <= 0;
    }

    /**
     * The map as one payload, for the app.
     *
     * Each world carries the content pack that holds it, so the app can fetch
     * a world the moment a child taps one of its missions rather than asking
     * them to download it first.
     */
    public function payloadFor(Child $child): array
    {
        $worlds = $this->worldsFor($child);
        $progress = $this->progressFor($child);

        $packs = ContentPack::latestVersions()->keyBy('world_id');

        return [
            'songs_unlocked'    => $this->songsUnlocked($child),
            'remaining_minutes' => ($child->daily_time_limit_minutes ?? 0) > 0 ? $child->remaining_time_minutes : null,
            'worlds'            => $worlds->map(function (AdventureWorld $world) use ($progress, $packs) {
                $pack = $packs->get($world->id);

                return [
                    'id'               => (int) $world->id,
                    'slug'             => $world->slug,
                    'name'             => $world->name,
                    'icon'             => $world->icon,
                    'subject_name'     => $world->subject_name,
                    'subject_category' => $world->subject_category,
                    'pack'             => $pack ? ['pack_id' => $pack->pack_id, 'version' => (int) $pack->version, 'sha256' => $pack->sha256] : null,
                    'missions'         => $world->missions->values()->map(fn ($mission) => [
                        'id'     => (int) $mission->id,
                        'title'  => $mission->title,
                        'status' => $progress['status'][$mission->id] ?? null,
                        'stars'  => (int) ($progress['stars'][$mission->id] ?? 0),
                    ])->all(),
                ];
            })->values()->all(),
        ];
    }
}
