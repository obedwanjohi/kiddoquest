<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $admin = Auth::guard('admin')->user();

            if (! $admin->is_active) {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'This account has been deactivated.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister(): View|\Illuminate\Http\RedirectResponse
    {
        // Auto-lock: If an admin already exists, lock the setup screen
        if (\App\Models\Admin::count() > 0) {
            return redirect()->route('admin.login')->with('error', '🔒 Admin registration is locked. Please sign in with your credentials.');
        }

        return view('admin.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // Security check: Lock if admin already exists
        if (\App\Models\Admin::count() > 0) {
            return redirect()->route('admin.login')->with('error', '🔒 Admin registration is locked.');
        }

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = \App\Models\Admin::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        Auth::guard('admin')->login($admin);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', '🎉 Welcome Super Admin! Your account is created and admin registration is now securely locked.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}