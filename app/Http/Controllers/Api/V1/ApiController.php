<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Shared plumbing for the app API: one error shape, one child accessor.
 */
abstract class ApiController extends Controller
{
    protected function guardian(Request $request): Guardian
    {
        /** @var Guardian $guardian */
        $guardian = $request->user();

        return $guardian;
    }

    protected function child(Request $request): ?Child
    {
        $child = $request->attributes->get('child');

        return $child instanceof Child ? $child : null;
    }

    protected function deviceId(Request $request): string
    {
        return (string) ($request->attributes->get('device_id') ?: $request->header('X-Device-Id') ?: 'unknown');
    }

    /**
     * Every failure the app sees looks like this, so one handler in Dart covers
     * all of them.
     */
    protected function fail(string $code, string $message, int $status = 400, array $fields = []): JsonResponse
    {
        return response()->json([
            'error' => array_filter([
                'code'    => $code,
                'message' => $message,
                'fields'  => $fields ?: null,
            ]),
        ], $status);
    }

    protected function childArray(Child $child): array
    {
        return [
            'id'                       => (int) $child->id,
            'name'                     => $child->name,
            'avatar'                   => $child->avatar,
            'avatar_emoji'             => $child->avatar_emoji,
            'level'                    => $child->recommended_level,
            'favorite_color'           => $child->favorite_color,
            'birthdate'                => optional($child->birthdate)->toDateString(),
            'total_stars'              => (int) $child->total_stars,
            'star_coins'               => (int) $child->star_coins,
            'streak_days'              => (int) ($child->streak_days ?? 0),
            'unlocked_items'           => $child->unlocked_items ?? [],
            'equipped_hat'             => $child->equipped_hat,
            'daily_time_limit_minutes' => (int) ($child->daily_time_limit_minutes ?? 0),
            'assigned_mission_id'      => $child->assigned_mission_id ? (int) $child->assigned_mission_id : null,
            'last_played_at'           => optional($child->last_played_at)->toIso8601String(),
        ];
    }
}
