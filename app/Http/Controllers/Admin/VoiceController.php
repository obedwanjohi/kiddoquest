<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voice;
use Illuminate\Http\Request;

class VoiceController extends Controller
{
    public function index()
    {
        $voices = Voice::withCount('lessons')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.voices.index', compact('voices'));
    }

    public function create()
    {
        return view('admin.voices.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateVoice($request);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $voice = Voice::create($validated);

        return redirect()
            ->route('admin.voices.index')
            ->with('success', "Voice \"{$voice->name}\" created.");
    }

    public function edit(Voice $voice)
    {
        return view('admin.voices.edit', compact('voice'));
    }

    public function update(Request $request, Voice $voice)
    {
        $validated = $this->validateVoice($request);

        $voice->update($validated);

        return redirect()
            ->route('admin.voices.index')
            ->with('success', "Voice \"{$voice->name}\" updated.");
    }

    public function toggle(Voice $voice)
    {
        $voice->update(['status' => $voice->status === 'active' ? 'inactive' : 'active']);

        return back()->with('success', "Voice \"{$voice->name}\" is now {$voice->status}.");
    }

    public function destroy(Voice $voice)
    {
        if ($voice->lessons()->count() > 0) {
            return back()->with('error', "Cannot delete \"{$voice->name}\" — it is used by {$voice->lessons()->count()} lesson(s). Reassign those first or deactivate it instead.");
        }

        $name = $voice->name;
        $voice->delete();

        return redirect()
            ->route('admin.voices.index')
            ->with('success', "Voice \"{$name}\" deleted.");
    }

    private function validateVoice(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string|max:50',
            'voice_id' => 'nullable|string|max:255',
            'language' => 'required|string|max:10',
            'gender' => 'nullable|in:male,female,neutral',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
