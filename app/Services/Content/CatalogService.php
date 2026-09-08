<?php

namespace App\Services\Content;

use App\Models\ContentPack;
use Illuminate\Support\Facades\Cache;

/**
 * What a device is offered to download.
 *
 * Cross-level worlds (tracing, speak and repeat) carry no level code and are
 * offered to every child, which is how the website's map behaves too.
 */
class CatalogService
{
    public function forLevel(?string $levelCode = null): array
    {
        $packs = Cache::remember('content:catalog', now()->addMinutes(5), function () {
            return ContentPack::latestVersions()
                ->map(fn (ContentPack $pack) => $pack->toCatalogArray())
                ->values()
                ->all();
        });

        if ($levelCode) {
            $wanted = strtoupper($levelCode);
            $packs = array_values(array_filter(
                $packs,
                fn (array $pack) => $pack['level'] === null || strtoupper((string) $pack['level']) === $wanted
            ));
        }

        usort($packs, function (array $a, array $b) {
            return [$a['subject_code'] ?? '', $a['sort_order'], $a['name']]
                <=> [$b['subject_code'] ?? '', $b['sort_order'], $b['name']];
        });

        return $packs;
    }

    /**
     * A weak ETag over the catalog so a device on a slow link can skip the body.
     */
    public function etagFor(array $packs): string
    {
        $signature = array_map(fn (array $pack) => $pack['pack_id'] . ':' . $pack['version'], $packs);

        return 'W/"' . substr(sha1(implode('|', $signature)), 0, 32) . '"';
    }
}
