<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SoundController extends Controller
{
    /**
     * Display the sounds management page.
     */
    public function index()
    {
        $correctPath = public_path('sounds/correct');
        $wrongPath = public_path('sounds/wrong');

        if (!File::exists($correctPath)) File::makeDirectory($correctPath, 0755, true);
        if (!File::exists($wrongPath)) File::makeDirectory($wrongPath, 0755, true);

        // Fetch existing sounds
        $correctSounds = collect(File::files($correctPath))
            ->filter(fn($file) => $file->getExtension() === 'mp3')
            ->map(fn($file) => $file->getFilename())
            ->sort()
            ->values();

        $wrongSounds = collect(File::files($wrongPath))
            ->filter(fn($file) => $file->getExtension() === 'mp3')
            ->map(fn($file) => $file->getFilename())
            ->sort()
            ->values();

        return view('admin.sounds.index', compact('correctSounds', 'wrongSounds'));
    }

    /**
     * Upload a new sound.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:correct,wrong',
            'sound' => 'required|file|mimes:mp3|max:5120', // max 5MB
        ]);

        $type = $request->input('type');
        $path = public_path("sounds/{$type}");

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        // Find next available slot
        $files = collect(File::files($path))
            ->filter(fn($file) => $file->getExtension() === 'mp3');

        if ($files->count() >= 5) {
            return back()->with('error', 'You can only upload up to 5 sounds per category.');
        }

        // Find lowest available number from 1 to 5
        $existingNumbers = $files->map(fn($file) => (int) $file->getFilenameWithoutExtension())->toArray();
        $nextNum = 1;
        while (in_array($nextNum, $existingNumbers) && $nextNum <= 5) {
            $nextNum++;
        }

        $fileName = "{$nextNum}.mp3";
        $request->file('sound')->move($path, $fileName);

        return back()->with('success', 'Sound uploaded successfully!');
    }

    /**
     * Delete a sound.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'type' => 'required|in:correct,wrong',
            'filename' => 'required|string',
        ]);

        $type = $request->input('type');
        $filename = $request->input('filename');
        $path = public_path("sounds/{$type}/{$filename}");

        // Security check: only delete mp3 files in the specific directories
        if (File::exists($path) && pathinfo($path, PATHINFO_EXTENSION) === 'mp3') {
            File::delete($path);
            return back()->with('success', 'Sound deleted successfully!');
        }

        return back()->with('error', 'Sound not found.');
    }
}
