<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Everything the app needs to know that can change without a release: prices,
 * question caps, feature flags, and the oldest build still allowed to run.
 */
class ConfigController extends ApiController
{
    public function show(Request $request): JsonResponse
    {
        $plans = collect(config('plans.plans', []))
            ->map(fn (array $plan, string $key) => [
                'key'       => $key,
                'name'      => $plan['name'] ?? ucfirst($key),
                'blurb'     => $plan['blurb'] ?? null,
                'emoji'     => $plan['emoji'] ?? null,
                'amount'    => (int) ($plan['amount'] ?? 0),
                'days'      => (int) ($plan['days'] ?? 30),
                'badge'     => $plan['badge'] ?? null,
                'highlight' => (bool) ($plan['highlight'] ?? false),
            ])
            ->values()
            ->all();

        $payload = [
            'min_app_version' => config('kiddoquest.min_app_version'),
            'timezone'        => config('kiddoquest.timezone'),
            'currency'        => config('plans.currency', 'KES'),
            'plans'           => $plans,
            'subscription'    => [
                'enforced'           => (bool) config('plans.enforce_subscription', false),
                'offline_grace_days' => (int) config('kiddoquest.entitlement.offline_grace_days', 7),
                'paybill'            => config('services.mpesa.shortcode'),
            ],
            'session' => [
                'questions_per_level'    => config('kiddoquest.session.questions_per_level'),
                'questions_default'      => config('kiddoquest.session.questions_default'),
                'pass_threshold_percent' => config('kiddoquest.session.pass_threshold_percent'),
                'exclusion_window_days'  => config('kiddoquest.session.exclusion_window_days'),
            ],
            'rewards'  => config('kiddoquest.rewards'),
            'shop'     => config('kiddoquest.shop'),
            'sync'     => [
                'max_events_per_request' => (int) config('kiddoquest.sync.max_events_per_request', 200),
            ],
            'features'           => config('kiddoquest.features'),
            'maintenance_banner' => config('kiddoquest.maintenance_banner'),
        ];

        $etag = 'W/"' . substr(sha1(json_encode($payload)), 0, 32) . '"';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response()->json(null, 304);
        }

        return response()->json($payload)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=300');
    }
}
