<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\SupabaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SoundController extends Controller
{
    /**
     * Display the game sounds management page (Correct, Wrong, Celebration).
     */
    public function index()
    {
        $correctSounds = Media::where('type', 'ILIKE', '%audio%')
            ->where('name', 'ILIKE', '%correct%')
            ->orderBy('id', 'desc')
            ->get();

        $wrongSounds = Media::where('type', 'ILIKE', '%audio%')
            ->where('name', 'ILIKE', '%wrong%')
            ->orderBy('id', 'desc')
            ->get();

        $celebSounds = Media::where('type', 'ILIKE', '%audio%')
            ->where('name', 'ILIKE', '%celebration%')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.sounds.index', compact('correctSounds', 'wrongSounds', 'celebSounds'));
    }

    /**
     * Upload a new sound (MP3, WAV, OGG, M4A).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'type' => 'required|in:correct,wrong,celebration',
            'sound' => 'required|file|max:10240', // max 10MB
        ]);

        $type = $request->input('type');
        $file = $request->file('sound');

        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        $folder = "media/audios";
        $randomName = Str::random(40);
        $fileName = $randomName . '.' . $extension;
        $filePath = $file->storeAs($folder, $fileName, 'public');

        // Upload to Supabase Storage Bucket
        $supabase = app(SupabaseStorageService::class);
        $localFileToRead = storage_path("app/public/{$filePath}");
        if (file_exists($localFileToRead)) {
            $supabaseUrl = $supabase->uploadFile($filePath, file_get_contents($localFileToRead), $mimeType);
            if ($supabaseUrl) {
                @unlink($localFileToRead);
            }
        }

        Media::create([
            'uploaded_by' => auth('admin')->id(),
            'name' => "{$type}.{$extension}",
            'disk' => 'public',
            'file_path' => $filePath,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'type' => 'audio',
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', ucfirst($type) . ' sound uploaded successfully to Supabase!');
    }

    /**
     * Delete a sound from database & Supabase.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:media,id',
        ]);

        $media = Media::find($request->input('id'));
        if ($media) {
            $media->delete();
            return back()->with('success', 'Sound deleted successfully!');
        }

        return back()->with('error', 'Sound not found.');
    }
}
