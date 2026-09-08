<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Parent sign-in for the app. Children never have accounts; a parent signs in
 * once per device and then picks who is playing.
 */
class AuthController extends ApiController
{
    /** How long an app session lasts before the parent has to sign in again. */
    protected const TOKEN_DAYS = 30;

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('guardians', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'phone'    => ['nullable', 'string', 'max:32'],
        ]);

        $guardian = Guardian::create([
            'name'       => $data['name'],
            'email'      => strtolower($data['email']),
            'password'   => $data['password'],
            'phone'      => $data['phone'] ?? null,
            'is_active'  => true,
            'parent_pin' => config('plans.default_parent_pin', '1234'),
        ]);

        return response()->json([
            'guardian'   => $this->guardianArray($guardian),
            'token'      => $this->issueToken($guardian, $request),
            'starter_pin'=> config('plans.default_parent_pin', '1234'),
            'children'   => [],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = 'api-login:' . Str::lower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return $this->fail('too_many_attempts', 'Too many sign-in attempts. Try again in a minute.', 429);
        }

        $guardian = Guardian::where('email', strtolower($data['email']))->first();

        if (! $guardian || ! Hash::check($data['password'], $guardian->password)) {
            RateLimiter::hit($key, 60);

            return $this->fail('invalid_credentials', 'That email and password do not match.', 401);
        }

        if (! $guardian->is_active) {
            return $this->fail('account_disabled', 'This account has been deactivated.', 403);
        }

        RateLimiter::clear($key);

        return response()->json([
            'guardian' => $this->guardianArray($guardian),
            'token'    => $this->issueToken($guardian, $request),
            'children' => $guardian->children()->orderBy('created_at')->get()->map(fn ($child) => $this->childArray($child)),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $guardian = $this->guardian($request);

        return response()->json([
            'guardian' => $this->guardianArray($guardian),
            'children' => $guardian->children()->orderBy('created_at')->get()->map(fn ($child) => $this->childArray($child)),
        ]);
    }

    /**
     * Swap a token that is still valid for a fresh one, so a family that opens
     * the app regularly never gets signed out.
     */
    public function refresh(Request $request): JsonResponse
    {
        $guardian = $this->guardian($request);
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['token' => $this->issueToken($guardian, $request)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $this->guardian($request)->tokens()->delete();

        return response()->json(['ok' => true]);
    }

    protected function issueToken(Guardian $guardian, Request $request): array
    {
        $name = Str::limit((string) ($request->header('X-Device-Model') ?: $request->header('X-Device-Id') ?: 'app'), 40, '');
        $expiresAt = now()->addDays(self::TOKEN_DAYS);

        $token = $guardian->createToken($name ?: 'app', ['parent'], $expiresAt);

        return [
            'access_token' => $token->plainTextToken,
            'expires_at'   => $expiresAt->toIso8601String(),
            'abilities'    => ['parent'],
        ];
    }

    protected function guardianArray(Guardian $guardian): array
    {
        return [
            'id'                => (int) $guardian->id,
            'name'              => $guardian->name,
            'email'             => $guardian->email,
            'phone'             => $guardian->phone,
            'has_custom_pin'    => $guardian->hasCustomPin(),
            'enable_devotional' => (bool) ($guardian->enable_devotional ?? true),
            'enable_songs_hub'  => (bool) ($guardian->enable_songs_hub ?? true),
        ];
    }
}
