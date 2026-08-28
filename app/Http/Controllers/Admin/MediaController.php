<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::with(['subject', 'uploadedBy']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $mediaItems = $query->orderByDesc('created_at')->paginate(24)->withQueryString();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.media.index', compact('mediaItems', 'subjects'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.media.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'files.*' => 'required|file|max:102400', // 100MB max per file
            'files' => 'required|array',
            'name' => 'nullable|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'tags' => 'nullable|string',
            'alt_text' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $files = $request->file('files');
        $uploadedCount = 0;

        foreach ($files as $file) {
            if (!$file) continue;

            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            $type = $this->detectType($mimeType, $extension);

            $folder = "media/{$type}s";
            $randomName = Str::random(40);
            $fileName = $randomName . '.' . $extension;
            $filePath = $file->storeAs($folder, $fileName, 'public');

            $width = $height = null;
            $sizeBytes = $file->getSize();

            if ($type === 'image') {
                $absSourcePath = storage_path("app/public/{$filePath}");
                $webpFileName = $randomName . '.webp';
                $absWebpPath = storage_path("app/public/media/images/{$webpFileName}");

                $optResult = \App\Services\ImageOptimizer::optimizeAndConvertToWebp($absSourcePath, $absWebpPath, 512, 80);

                if ($optResult !== false) {
                    // Delete original raw uncompressed file if format changed
                    if ($extension !== 'webp' && file_exists($absSourcePath)) {
                        @unlink($absSourcePath);
                    }

                    $filePath = "media/images/{$webpFileName}";
                    $extension = 'webp';
                    $mimeType = 'image/webp';
                    $width = $optResult['width'];
                    $height = $optResult['height'];
                    $sizeBytes = $optResult['size_bytes'];
                } else {
                    $imageInfo = @getimagesize($absSourcePath);
                    if ($imageInfo) {
                        $width = $imageInfo[0];
                        $height = $imageInfo[1];
                    }
                }
            // Upload to Supabase Storage Bucket
            $supabase = app(\App\Services\SupabaseStorageService::class);
            $localFileToRead = storage_path("app/public/{$filePath}");
            if (file_exists($localFileToRead)) {
                $supabaseUrl = $supabase->uploadFile($filePath, file_get_contents($localFileToRead), $mimeType);
                if ($supabaseUrl) {
                    @unlink($localFileToRead);
                }
            }

            // Only use the explicitly provided name if a single file was uploaded
            $mediaName = (count($files) === 1 && !empty($validated['name'])) 
                ? $validated['name'] 
                : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            Media::create([
                'uploaded_by' => auth('admin')->id(),
                'name' => $mediaName,
                'disk' => 'public',
                'file_path' => $filePath,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'extension' => $extension,
                'type' => $type,
                'size_bytes' => $sizeBytes,
                'width' => $width,
                'height' => $height,
                'subject_id' => $validated['subject_id'] ?? null,
                'tags' => $validated['tags'] ? array_filter(array_map('trim', explode(',', $validated['tags']))) : null,
                'alt_text' => $validated['alt_text'] ?? null,
                'description' => $validated['description'] ?? null,
            ]);
            
            $uploadedCount++;
        }

        return redirect()->route('admin.media.index')
            ->with('success', "{$uploadedCount} media file(s) uploaded successfully!");
    }

    public function show(Media $media)
    {
        $media->load(['subject', 'uploadedBy']);
        return view('admin.media.show', compact('media'));
    }

    public function edit(Media $media)
    {
        $subjects = Subject::orderBy('name')->get();
        return view('admin.media.edit', compact('media', 'subjects'));
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject_id' => 'nullable|exists:subjects,id',
            'tags' => 'nullable|string',
            'alt_text' => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $validated['tags'] = $validated['tags'] ?? null
            ? array_filter(array_map('trim', explode(',', $validated['tags'])))
            : null;

        $media->update($validated);

        return redirect()->route('admin.media.show', $media)
            ->with('success', 'Media updated!');
    }

    public function destroy(Media $media)
    {
        // Delete the physical file
        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }
        if ($media->thumbnail_path && Storage::disk('public')->exists($media->thumbnail_path)) {
            Storage::disk('public')->delete($media->thumbnail_path);
        }

        $name = $media->name;
        $media->delete();

        return redirect()->route('admin.media.index')
            ->with('success', "Media \"{$name}\" deleted.");
    }

    // ── Helpers ────────────────────────────────────────────────

    private function detectType(string $mimeType, string $extension): string
    {
        if (str_starts_with($mimeType, 'image/')) return 'image';
        if (str_starts_with($mimeType, 'video/')) return 'video';
        if (str_starts_with($mimeType, 'audio/')) return 'audio';

        $docExtensions = ['pdf', 'doc', 'docx', 'txt', 'csv', 'xlsx', 'ppt', 'pptx'];
        if (in_array($extension, $docExtensions)) return 'document';

        return 'document';
    }

    // ── JSON API for Media Picker ──────────────────────────────
    // Returns media items as JSON for the quiz builder modal picker.
    public function searchApi(Request $request)
    {
        $query = Media::query();

        // Filter by type (image, audio, video, all)
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by subject
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // Search by name or tags
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('alt_text', 'like', $term);
            });
        }

        $items = $query->orderByDesc('created_at')->limit(60)->get();

        return response()->json([
            'items' => $items->map(function ($m) {
                return [
                    'id'           => $m->id,
                    'name'         => $m->name,
                    'type'         => $m->type,
                    'icon'         => $m->icon,
                    'url'          => $m->url,
                    'thumb_url'    => $m->thumbnail_url ?: $m->url,
                    'width'        => $m->width,
                    'height'       => $m->height,
                    'duration'     => $m->duration_formatted,
                    'size'         => $m->size_formatted,
                    'alt_text'     => $m->alt_text,
                    'aspect_ratio' => $m->width && $m->height
                        ? round($m->width / $m->height, 2)
                        : null,
                ];
            }),
            'total' => $items->count(),
        ]);
    }
}
