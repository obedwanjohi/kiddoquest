<?php

namespace App\Http\Middleware;

use App\Models\AdventureWorld;
use App\Models\Mission;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates paid adventure worlds behind an active subscription.
 *
 * - Off unless SUBSCRIPTION_ENFORCE=true (config/plans.php), so nothing changes
 *   for testing until the switch is flipped.
 * - The first world (lowest sort_order) of every subject is free, plus any slug in
 *   config('plans.free_world_slugs'). (The old rule was "world id === 1".)
 * - Never falls back to Guardian::first(); an anonymous request is simply not entitled.
 */
class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('plans.enforce_subscription', false)) {
            return $next($request);
        }

        $world = $request->route('world');
        $mission = $request->route('mission');

        if (! $world instanceof AdventureWorld && $mission instanceof Mission) {
            $world = $mission->adventureWorld;
        }

        if (! $world instanceof AdventureWorld || self::isFreeWorld($world)) {
            return $next($request);
        }

        $guardian = Auth::guard('guardian')->user();

        if ($guardian && self::hasActiveSubscription($guardian->id)) {
            return $next($request);
        }

        return redirect()
            ->route('parent.pin_gate')
            ->with('error', "🔒 {$world->name} needs an active subscription. A parent can unlock it from the Parent Zone.");
    }

    public static function hasActiveSubscription(int $guardianId): bool
    {
        return Subscription::where('guardian_id', $guardianId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public static function isFreeWorld(AdventureWorld $world): bool
    {
        if (in_array($world->slug, (array) config('plans.free_world_slugs', []), true)) {
            return true;
        }

        if (! $world->subject_id) {
            return true;
        }

        static $firstBySubject = [];

        if (! array_key_exists($world->subject_id, $firstBySubject)) {
            $firstBySubject[$world->subject_id] = AdventureWorld::where('subject_id', $world->subject_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->value('id');
        }

        return $firstBySubject[$world->subject_id] === $world->id;
    }
}
