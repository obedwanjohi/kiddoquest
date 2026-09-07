<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Everything past the Parent Zone PIN gate must have been unlocked recently.
 * Previously only the dashboard page checked the flag; changing the PIN,
 * screen-time, focus missions, the AI coach and M-Pesa checkout did not.
 */
class EnsureParentUnlocked
{
    public const TIMEOUT_MINUTES = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $unlockedAt = session('parent_unlocked_at');
        $fresh = false;

        if (session('parent_unlocked') && $unlockedAt) {
            try {
                $fresh = Carbon::parse($unlockedAt)->diffInMinutes(now()) <= self::TIMEOUT_MINUTES;
            } catch (\Throwable) {
                $fresh = false;
            }
        }

        if (! $fresh) {
            session()->forget(['parent_unlocked', 'parent_unlocked_at']);
            $message = $unlockedAt
                ? 'Parent Zone locked after ' . self::TIMEOUT_MINUTES . ' minutes. Enter your PIN again.'
                : 'Enter your Parent PIN to continue.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $message], 403);
            }

            return redirect()->route('parent.pin_gate')->with('info', $message);
        }

        return $next($request);
    }
}
