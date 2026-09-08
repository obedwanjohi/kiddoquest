<?php

namespace App\Http\Middleware\Api;

use App\Models\Child;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads X-Child-Id and puts the child on the request, but only if the signed-in
 * guardian owns them. Every endpoint that touches a child's data goes through
 * here, so ownership is checked in one place rather than in each controller.
 */
class ResolveChild
{
    public function handle(Request $request, Closure $next, string $requirement = 'required'): Response
    {
        $childId = $request->header('X-Child-Id') ?: $request->input('child_id');

        if (! $childId) {
            if ($requirement === 'optional') {
                return $next($request);
            }

            return response()->json([
                'error' => ['code' => 'child_required', 'message' => 'Send the child id in the X-Child-Id header.'],
            ], 400);
        }

        $guardian = $request->user();

        $child = $guardian
            ? Child::where('guardian_id', $guardian->id)->find((int) $childId)
            : null;

        if (! $child) {
            return response()->json([
                'error' => ['code' => 'child_not_found', 'message' => 'That child does not belong to this account.'],
            ], 404);
        }

        $request->attributes->set('child', $child);

        return $next($request);
    }
}
