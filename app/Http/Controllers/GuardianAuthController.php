<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GuardianAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('guardian.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('guardian')->attempt($credentials, $request->boolean('remember'))) {
            $guardian = Auth::guard('guardian')->user();

            if (! $guardian->is_active) {
                Auth::guard('guardian')->logout();
                $request->session()->invalidate();
                return back()->withErrors(['email' => 'This account has been deactivated.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            // Redirect directly to Kid UI Profile Selector ("Who's Playing?")
            return redirect()->route('kids.profiles')->with('success', "Welcome back! Who is playing today?");
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('guardian.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:guardians,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ]);

        $guardian = Guardian::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => $validated['password'],
            'phone'    => $validated['phone'] ?? null,
        ]);

        Auth::guard('guardian')->login($guardian);
        $request->session()->regenerate();

        // Redirect directly to Add Child Profile
        return redirect()->route('guardian.children.create')->with('success', 'Welcome to KiddoQuest CBC! Let\'s add your child\'s profile.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('guardian')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}