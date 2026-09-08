<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Middleware\EnsureActiveSubscription;
use App\Models\ContentPack;
use App\Services\Content\CatalogService;
use App\Services\Content\ExtrasRegistry;
use App\Services\Content\MediaResolver;
use App\Services\Learning\ChildSnapshotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * What there is to download, and where to get it.
 */
class ContentController extends ApiController
{
    public function catalog(Request $request, CatalogService $catalog): JsonResponse
    {
        $level = $request->query('level');
        $child = $this->child($request);

        if (! $level && $child) {
            $level = $this->levelCodeFor($child->recommended_level);
        }

        $packs = $catalog->forLevel($level);
        $etag = $catalog->etagFor($packs);

        if ($request->headers->get('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()->json([
            'level' => $level,
            'packs' => $packs,
        ])->header('ETag', $etag)->header('Cache-Control', 'private, max-age=300');
    }

    /**
     * The pack itself, plus the media list. Paid worlds are only handed out to a
     * family that is entitled to them; free worlds are open so a new install can
     * start playing before anyone has paid.
     */
    public function manifest(Request $request, string $packId, ChildSnapshotService $snapshots, MediaResolver $resolver): JsonResponse
    {
        $pack = ContentPack::where('pack_id', $packId)
            ->when($request->query('version'), fn ($query, $version) => $query->where('version', (int) $version))
            ->orderByDesc('version')
            ->first();

        if (! $pack) {
            return $this->fail('pack_not_found', 'No published pack with that id.', 404);
        }

        if (! $pack->is_free && config('plans.enforce_subscription', false)) {
            $entitlement = $snapshots->entitlement($this->guardian($request));

            if (($entitlement['status'] ?? 'none') !== 'active') {
                return $this->fail('subscription_required', 'This world needs an active subscription.', 402);
            }
        }

        $disk = Storage::disk(config('kiddoquest.content.disk', 'public'));

        if (! $disk->exists($pack->json_path)) {
            return $this->fail('pack_missing', 'The pack file is not on the storage disk. Re-publish it.', 410);
        }

        $document = json_decode((string) $disk->get($pack->json_path), true);

        return response()->json([
            'pack'  => $pack->toCatalogArray(),
            'url'   => $pack->url ?: $resolver->publicUrl($pack->json_path),
            'media' => $document['media'] ?? [],
        ]);
    }

    /**
     * Serve the pack document itself. In production this is a CDN object; this
     * route exists so a local build and a beta deploy work without one.
     */
    public function download(Request $request, string $packId, int $version)
    {
        $pack = ContentPack::where('pack_id', $packId)->where('version', $version)->first();

        if (! $pack) {
            return $this->fail('pack_not_found', 'No published pack with that id and version.', 404);
        }

        $disk = Storage::disk(config('kiddoquest.content.disk', 'public'));

        if (! $disk->exists($pack->json_path)) {
            return $this->fail('pack_missing', 'The pack file is not on the storage disk.', 410);
        }

        return response($disk->get($pack->json_path), 200, [
            'Content-Type'  => 'application/json',
            'ETag'          => '"' . $pack->sha256 . '"',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * The devotional and the songs hub, in one small payload.
     *
     * The whole devotional list travels rather than today's, because the app
     * picks today itself: a bedtime devotional must not depend on a connection.
     * Whether a guardian has switched either off is answered here too, so the
     * app does not have to reason about it.
     */
    public function extras(Request $request): JsonResponse
    {
        $guardian = $this->guardian($request);

        return response()->json([
            'devotional' => [
                'enabled' => (bool) $guardian->enable_devotional,
                'items'   => ExtrasRegistry::devotionals(),
            ],
            'songs' => [
                'enabled' => (bool) $guardian->enable_songs_hub,
                'items'   => ExtrasRegistry::songs(),
            ],
        ]);
    }

    /**
     * Worlds a child may play right now, used by the map when a pack has not
     * been downloaded yet.
     */
    public function worlds(Request $request): JsonResponse
    {
        $child = $this->child($request);
        $level = $child ? $this->levelCodeFor($child->recommended_level) : $request->query('level');

        $worlds = \App\Models\AdventureWorld::with('subject.level')
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($world) use ($level) {
                $worldLevel = $world->subject?->level?->code;

                return $level === null || $worldLevel === null || strtoupper((string) $worldLevel) === strtoupper((string) $level);
            })
            ->map(fn ($world) => [
                'id'          => (int) $world->id,
                'slug'        => $world->slug,
                'name'        => $world->name,
                'icon'        => $world->icon,
                'theme_color' => $world->theme_color,
                'category'    => $world->subject_category,
                'subject'     => $world->subject->name ?? null,
                'level'       => $world->subject?->level?->code,
                'sort_order'  => (int) $world->sort_order,
                'is_free'     => EnsureActiveSubscription::isFreeWorld($world),
                'missions'    => $world->missions()->count(),
            ])
            ->values();

        return response()->json(['level' => $level, 'worlds' => $worlds]);
    }

    /**
     * The database stores levels as "Play Group" / "PP1"; packs use codes.
     */
    protected function levelCodeFor(?string $level): ?string
    {
        if (! $level) {
            return null;
        }

        $normalized = strtoupper(trim($level));

        return match (true) {
            str_contains($normalized, 'PLAY')  => 'PG',
            $normalized === 'PG'               => 'PG',
            str_contains($normalized, 'PP1')   => 'PP1',
            str_contains($normalized, 'PP2')   => 'PP2',
            default                            => $normalized,
        };
    }
}
