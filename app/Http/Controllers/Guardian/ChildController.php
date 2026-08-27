<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ChildController extends Controller
{
    /**
     * Available favorite colors for personalization.
     */
    protected const COLORS = [
        'purple' => '#A855F7',
        'blue'   => '#3B82F6',
        'green'  => '#22C55E',
        'pink'   => '#EC4899',
        'orange' => '#F97316',
        'yellow' => '#EAB308',
        'red'    => '#EF4444',
        'teal'   => '#14B8A6',
    ];

    /**
     * Show the form for creating a new child.
     */
    public function create()
    {
        $guardian = Auth::guard('guardian')->user();
        if (! $guardian) {
            return redirect()->route('guardian.login')->with('error', 'Please sign in to add a child.');
        }

        return view('guardian.children.create', [
            'avatars' => Child::AVATARS,
            'colors'  => self::COLORS,
        ]);
    }

    /**
     * Store a newly created child.
     */
    public function store(Request $request): RedirectResponse
    {
        $guardian = Auth::guard('guardian')->user();
        if (! $guardian) {
            return redirect()->route('guardian.login')->with('error', 'Please sign in to add a child.');
        }

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'avatar'         => ['required', 'string', Rule::in(Child::avatarIdentifiers())],
            'favorite_color' => ['nullable', 'string', Rule::in(array_keys(self::COLORS))],
            'birthdate'      => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
        ]);

        $child = $guardian->children()->create([
            'name'              => trim($validated['name']),
            'avatar'            => $validated['avatar'],
            'favorite_color'    => $validated['favorite_color'] ?? 'purple',
            'birthdate'         => $validated['birthdate'] ?? null,
            'recommended_level' => Child::recommendLevel($validated['birthdate'] ?? null),
            'total_stars'       => 0,
            'star_coins'        => 0,
        ]);

        // Redirect to the magical onboarding welcome screen
        return redirect()
            ->route('guardian.children.welcome', $child)
            ->with('success', "{$child->name}'s profile has been created! 🎉");
    }

    /**
     * Show the magical onboarding welcome screen (Leo greets the child).
     */
    public function welcome(Child $child): View
    {
        $this->authorizeChild($child);

        return view('guardian.children.welcome', [
            'child' => $child,
        ]);
    }

    /**
     * Show the form for editing a child.
     */
    public function edit(Child $child): View
    {
        $this->authorizeChild($child);

        return view('guardian.children.edit', [
            'child'  => $child,
            'avatars' => Child::AVATARS,
            'colors'  => self::COLORS,
        ]);
    }

    /**
     * Update the specified child.
     */
    public function update(Request $request, Child $child): RedirectResponse
    {
        $this->authorizeChild($child);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'avatar'         => ['required', 'string', Rule::in(Child::avatarIdentifiers())],
            'favorite_color' => ['nullable', 'string', Rule::in(array_keys(self::COLORS))],
            'birthdate'      => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
        ]);

        $child->update([
            'name'              => $validated['name'],
            'avatar'            => $validated['avatar'],
            'favorite_color'    => $validated['favorite_color'] ?? null,
            'birthdate'         => $validated['birthdate'] ?? null,
            'recommended_level' => Child::recommendLevel($validated['birthdate'] ?? null),
        ]);

        return redirect()
            ->route('guardian.dashboard')
            ->with('success', "{$child->name}'s profile has been updated! ✨");
    }

    /**
     * Remove the specified child.
     */
    public function destroy(Child $child): RedirectResponse
    {
        $this->authorizeChild($child);

        $name = $child->name;
        $child->delete();

        return redirect()
            ->route('guardian.dashboard')
            ->with('success', "{$name}'s profile has been removed.");
    }

    /**
     * Security: ensure the child belongs to the logged-in guardian.
     */
    protected function authorizeChild(Child $child): void
    {
        $guardian = Auth::guard('guardian')->user();

        if ($child->guardian_id !== $guardian->id) {
            abort(403, 'This child profile does not belong to you.');
        }
    }
}