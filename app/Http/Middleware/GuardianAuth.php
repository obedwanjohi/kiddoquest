<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuardianAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('guardian')->check()) {
            return redirect()->route('guardian.login');
        }

        $guardian = Auth::guard('guardian')->user();

        if (! $guardian->is_active) {
            Auth::guard('guardian')->logout();
            $request->session()->invalidate();
            return redirect()->route('guardian.login')
                ->with('error', 'Your account has been deactivated.');
        }

        return $next($request);
    }
}