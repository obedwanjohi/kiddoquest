<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Device;
use App\Models\MissionAttempt;
use App\Services\Learning\ParentReportService;
use App\Services\ParentAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

/**
 * The parent side of the app: the PIN gate and the settings behind it.
 *
 * The app checks the PIN against a cached hash so the gate works on a plane;
 * this endpoint is what seeds that hash and what a PIN change goes through.
 */
class ParentController extends ApiController
{
    protected const MAX_ATTEMPTS = 5;

    protected const LOCKOUT_SECONDS = 60;

    /**
     * Verify the PIN and hand back a short-lived token for the sensitive
     * endpoints, plus the hash the app stores for offline checks.
     */
    public function verifyPin(Request $request): JsonResponse
    {
        $data = $request->validate(['pin' => ['required', 'string', 'size:4']]);

        $guardian = $this->guardian($request);
        $key = 'api-parent-pin:' . $guardian->id;

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return $this->fail(
                'pin_locked',
                'Too many tries. Wait ' . RateLimiter::availableIn($key) . ' seconds.',
                429
            );
        }

        if (! $guardian->verifyPin($data['pin'])) {
            RateLimiter::hit($key, self::LOCKOUT_SECONDS);

            return $this->fail('pin_incorrect', 'That PIN is not right.', 401, [
                'attempts_left' => max(0, self::MAX_ATTEMPTS - RateLimiter::attempts($key)),
            ]);
        }

        RateLimiter::clear($key);

        $expiresAt = now()->addMinutes(30);
        $token = $guardian->createToken('parent-zone', ['parent', 'parent-admin'], $expiresAt);

        return response()->json([
            'token' => [
                'access_token' => $token->plainTextToken,
                'expires_at'   => $expiresAt->toIso8601String(),
                'abilities'    => ['parent', 'parent-admin'],
            ],
            // Lets the app gate itself offline without ever storing the PIN.
            'pin_hash'       => $guardian->getRawOriginal('parent_pin'),
            'has_custom_pin' => $guardian->hasCustomPin(),
        ]);
    }

    public function updatePin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'new_pin'  => ['required', 'string', 'size:4', 'regex:/^[0-9]{4}$/'],
            'password' => ['required', 'string'],
        ]);

        $guardian = $this->guardian($request);

        if (! Hash::check($data['password'], $guardian->password)) {
            return $this->fail('password_incorrect', 'Enter your account password to change the PIN.', 401);
        }

        $guardian->parent_pin = $data['new_pin'];
        $guardian->pin_changed_at = now();
        $guardian->save();

        return response()->json(['ok' => true, 'pin_hash' => $guardian->getRawOriginal('parent_pin')]);
    }

    /** Devotional and songs-hub toggles. */
    public function updateSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enable_devotional' => ['sometimes', 'boolean'],
            'enable_songs_hub'  => ['sometimes', 'boolean'],
        ]);

        $guardian = $this->guardian($request);

        if (array_key_exists('enable_devotional', $data)) {
            $guardian->enable_devotional = $data['enable_devotional'];
        }

        if (array_key_exists('enable_songs_hub', $data)) {
            $guardian->enable_songs_hub = $data['enable_songs_hub'];
        }

        $guardian->save();

        return response()->json([
            'settings' => [
                'enable_devotional' => (bool) $guardian->enable_devotional,
                'enable_songs_hub'  => (bool) $guardian->enable_songs_hub,
            ],
        ]);
    }

    /**
     * The report behind the parent dashboard.
     *
     * Read entirely from projections, so opening it costs a handful of indexed
     * queries rather than a walk through every event the child ever produced.
     */
    public function report(Request $request, int $child, ParentReportService $reports): JsonResponse
    {
        $model = $this->guardian($request)->children()->find($child);

        if (! $model) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $range = (string) $request->query('range', '7d');
        $days = match ($range) {
            '30d' => 30,
            '90d' => 90,
            default => 7,
        };

        return response()->json($reports->build($model, $days));
    }

    /**
     * The coach: a parent's question about their child, answered with that
     * child's real numbers in front of it.
     *
     * The same service the website uses, so an answer does not depend on which
     * screen the question was asked from. It falls back to a data-driven reply
     * when no LLM key is configured, which means this endpoint always answers
     * rather than sometimes failing.
     */
    public function coach(Request $request, ParentAiService $ai): JsonResponse
    {
        $data = $request->validate([
            'child_id' => ['required', 'integer'],
            'question' => ['nullable', 'string', 'max:500'],
        ]);

        $child = $this->guardian($request)->children()->find($data['child_id']);

        if (! $child) {
            return $this->fail('child_not_found', 'That child does not belong to this account.', 404);
        }

        $attempts = MissionAttempt::where('child_id', $child->id)->get();
        $sumTotal = (int) $attempts->sum('total');

        $answer = $ai->generateAdvice($child, [
            'accuracy_rate'   => $sumTotal > 0 ? (int) round(($attempts->sum('score') / $sumTotal) * 100) : 80,
            'passed_missions' => $attempts->where('passed', true)->pluck('mission_id')->unique()->count(),
            'total_missions'  => $attempts->pluck('mission_id')->unique()->count(),
        ], (string) ($data['question'] ?? ''));

        return response()->json(['answer' => $answer, 'child_id' => (int) $child->id]);
    }

    public function pushToken(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:255']]);

        $deviceId = $this->deviceId($request);

        if ($deviceId === 'unknown') {
            return $this->fail('device_required', 'Send the device id in the X-Device-Id header.', 400);
        }

        Device::updateOrCreate(
            ['id' => $deviceId],
            [
                'guardian_id'  => $this->guardian($request)->id,
                'push_token'   => $data['token'],
                'last_seen_at' => now(),
            ]
        );

        return response()->json(['ok' => true]);
    }
}
