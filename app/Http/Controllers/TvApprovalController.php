<?php

namespace App\Http\Controllers;

use App\Models\DeviceLoginCode;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Approving a television from a browser.
 *
 * The API hands the TV an `approve_url` pointing here, for the family who has
 * the website open but not the phone app installed. It does exactly what the
 * app's approve endpoint does and nothing more: it never issues the token
 * itself, it only marks the code as belonging to this guardian, and the
 * television still has to claim it.
 */
class TvApprovalController extends Controller
{
    public function show(): View
    {
        return view('tv.approve');
    }

    public function approve(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:12']]);

        $guardian = Auth::guard('guardian')->user();

        if (! $guardian) {
            return redirect()->route('guardian.login');
        }

        // A code is six characters out of a small alphabet. Without a limit a
        // browser could walk the space and adopt somebody else's television.
        $key = 'tv-approve:' . $guardian->id;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withErrors([
                'code' => 'Too many tries. Wait a minute and ask the TV for a new code.',
            ]);
        }

        $code = DeviceLoginCode::where('code', strtoupper(trim($data['code'])))->first();

        if (! $code || $code->isExpired()) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['code' => 'That code has expired. Ask the TV for a new one.']);
        }

        if ($code->claimed_at) {
            RateLimiter::hit($key, 60);

            return back()->withErrors(['code' => 'That code was already used.']);
        }

        RateLimiter::clear($key);

        $code->guardian_id = $guardian->id;
        $code->approved_at = now();
        $code->save();

        return back()->with('status', 'That television is signed in. It will be ready in a moment.');
    }
}
