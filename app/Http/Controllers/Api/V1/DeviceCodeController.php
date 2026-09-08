<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\DeviceLoginCode;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Signing in on a television.
 *
 * Typing an email and password with a remote control is miserable, so the TV
 * shows a six-character code and the parent approves it from the phone app.
 * The TV polls until it is approved, then claims a token exactly once.
 */
class DeviceCodeController extends ApiController
{
    /** Called by the TV: give me a code to show on screen. */
    public function create(Request $request): JsonResponse
    {
        $ttl = (int) config('kiddoquest.device_code.ttl_minutes', 10);

        $code = DeviceLoginCode::create([
            'code'       => DeviceLoginCode::generateCode((int) config('kiddoquest.device_code.code_length', 6)),
            'device_id'  => Str::limit((string) $request->header('X-Device-Id'), 64, '') ?: null,
            'platform'   => Str::limit((string) $request->header('X-Platform'), 24, '') ?: null,
            'expires_at' => now()->addMinutes($ttl),
        ]);

        return response()->json([
            'code'          => $code->code,
            'expires_at'    => $code->expires_at->toIso8601String(),
            'poll_seconds'  => (int) config('kiddoquest.device_code.poll_seconds', 3),
            'approve_url'   => rtrim((string) config('app.url'), '/') . '/tv',
        ], 201);
    }

    /** Called by the signed-in phone app: yes, that television is mine. */
    public function approve(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);

        $code = DeviceLoginCode::where('code', strtoupper(trim($data['code'])))->first();

        if (! $code || $code->isExpired()) {
            return $this->fail('code_invalid', 'That code has expired. Ask the TV for a new one.', 404);
        }

        if ($code->claimed_at) {
            return $this->fail('code_used', 'That code was already used.', 409);
        }

        $code->guardian_id = $this->guardian($request)->id;
        $code->approved_at = now();
        $code->save();

        return response()->json(['ok' => true]);
    }

    /** Called by the TV on a timer until the parent approves. */
    public function poll(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);

        $code = DeviceLoginCode::where('code', strtoupper(trim($data['code'])))->first();

        if (! $code || $code->isExpired()) {
            return response()->json(['status' => 'expired']);
        }

        if (! $code->isApproved()) {
            return response()->json(['status' => 'pending']);
        }

        if ($code->claimed_at) {
            return response()->json(['status' => 'used']);
        }

        /** @var Guardian $guardian */
        $guardian = Guardian::find($code->guardian_id);

        if (! $guardian) {
            return response()->json(['status' => 'expired']);
        }

        $expiresAt = now()->addDays(30);
        $token = $guardian->createToken('android-tv', ['parent'], $expiresAt);

        $code->claimed_at = now();
        $code->save();

        return response()->json([
            'status'   => 'approved',
            'token'    => [
                'access_token' => $token->plainTextToken,
                'expires_at'   => $expiresAt->toIso8601String(),
                'abilities'    => ['parent'],
            ],
            'guardian' => ['id' => (int) $guardian->id, 'name' => $guardian->name, 'email' => $guardian->email],
            'children' => $guardian->children()->orderBy('created_at')->get()->map(fn ($child) => $this->childArray($child)),
        ]);
    }
}
