<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureChildSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('active_child_id')) {
            return redirect()->route('kids.profiles');
        }

        // Validate that the child still belongs to the logged-in guardian
        $guardian = Auth::guard('guardian')->user();
        if (!$guardian) {
            session()->forget('active_child_id');
            return redirect()->route('kids.profiles');
        }

        $child = $guardian->children()->find(session('active_child_id'));

        if (! $child) {
            session()->forget('active_child_id');
            return redirect()->route('kids.profiles');
        }

        return $next($request);
    }
}