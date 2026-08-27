<?php

namespace App\Http\Middleware;

use App\Models\Guardian;
use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Allow World 1 (Free World) always
        $worldParam = $request->route('world');
        if ($worldParam) {
            $worldId = is_object($worldParam) ? $worldParam->id : (int) $worldParam;
            if ($worldId === 1) {
                return $next($request);
            }
        }

        // 2. Check Active Subscription for Guardian
        $guardian = Auth::guard('guardian')->user() ?? Guardian::first();
        
        $hasActiveSub = Subscription::where('guardian_id', $guardian->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();

        if ($hasActiveSub) {
            return $next($request);
        }

        // Redirect to M-Pesa Subscription Paywall Page
        return redirect()->route('parent.subscription')->with('error', '🔒 World 2 & 3 require an active M-Pesa subscription! Subscribe below to unlock.');
    }
}
