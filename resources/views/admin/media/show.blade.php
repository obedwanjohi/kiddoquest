@extends('admin.layouts.app')
@section('title', 'Media Detail')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>{{ $media->icon }} {{ $media->name }}</h3>
        <div>
            <a href="{{ route('admin.media.edit', $media) }}" class="btn btn-secondary" style="font-size:12px;">✏️ Edit</a>
            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary" style="font-size:12px;">← Library</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

            {{-- Preview --}}
            <div>
                <div style="background:#f1f5f9;border-radius:12px;padding:20px;text-align:center;min-height:300px;display:flex;align-items:center;justify-content:center;">
                    @if ($media->isImage())
                        <img src="{{ $media->url }}" alt="{{ $media->name }}" style="max-width:100%;border-radius:8px;">
                    @elseif ($media->isVideo())
                        <video controls style="max-width:100%;border-radius:8px;">
                            <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                        </video>
                    @elseif ($media->isAudio())
                        <div>
                            <div style="font-size:64px;">🔊</div>
                            <audio controls style="margin-top:16px;width:100%;">
                                <source src="{{ $media->url }}" type="{{ $media->mime_type }}">
                            </audio>
                        </div>
                    @else
                        <div>
                            <div style="font-size:64px;">📄</div>
                            <a href="{{ $media->url }}" download class="btn btn-primary" style="margin-top:16px;">⬇️ Download</a>
                        </div>
                    @endif
                </div>

                @if ($media->isImage() || $media->isVideo())
                    <div style="margin-top:8px;text-align:center;">
                        <a href="{{ $media->url }}" target="_blank" class="btn btn-secondary" style="font-size:12px;">🔗 Open Full Size</a>
                    </div>
                @endif
            </div>

            {{-- Metadata --}}
            <div>
                <table class="table" style="font-size:14px;">
                    <tr>
                        <td style="width:140px;color:#999;">Type</td>
                        <td><span class="badge badge-draft">{{ $media->icon }} {{ ucfirst($media->type) }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:#999;">File Name</td>
                        <td>{{ $media->file_name }}</td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Format</td>
                        <td>{{ strtoupper($media->extension) }} ({{ $media->mime_type }})</td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Size</td>
                        <td>{{ $media->size_formatted }}</td>
                    </tr>
                    @if ($media->width && $media->height)
                    <tr>
                        <td style="color:#999;">Dimensions</td>
                        <td>{{ $media->width }} × {{ $media->height }}px</td>
                    </tr>
                    @endif
                    @if ($media->duration_formatted)
                    <tr>
                        <td style="color:#999;">Duration</td>
                        <td>{{ $media->duration_formatted }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color:#999;">Subject</td>
                        <td>{{ $media->subject?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Uploaded By</td>
                        <td>{{ $media->uploadedBy?->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td style="color:#999;">Uploaded</td>
                        <td>{{ $media->created_at->format('M j, Y g:i A') }}</td>
                    </tr>
                </table>

                @if ($media->tags && count($media->tags) > 0)
                    <div style="margin-top:12px;">
                        <strong style="font-size:13px;">Tags:</strong>
                        @foreach ($media->tags as $tag)
                            <span style="display:inline-block;background:#e0e7ff;color:#4f46e5;padding:2px 10px;border-radius:12px;font-size:12px;margin:2px;">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                @if ($media->alt_text)
                    <div style="margin-top:12px;">
                        <strong style="font-size:13px;">Alt Text:</strong>
                        <p style="color:#666;margin:4px 0 0;">{{ $media->alt_text }}</p>
                    </div>
                @endif

                @if ($media->description)
                    <div style="margin-top:12px;">
                        <strong style="font-size:13px;">Description:</strong>
                        <p style="color:#666;margin:4px 0 0;">{{ $media->description }}</p>
                    </div>
                @endif

                {{-- File Path (for copy-paste into lessons/quizzes) --}}
                <div style="margin-top:16px;padding:12px;background:#f8fafc;border-radius:8px;">
                    <strong style="font-size:12px;color:#999;">📎 File URL (copy to use in lessons):</strong>
                    <input type="text" readonly value="{{ $media->url }}"
                        style="width:100%;margin-top:4px;font-family:monospace;font-size:12px;background:white;border:1px solid #e5e7eb;padding:6px 8px;border-radius:4px;"
                        onclick="this.select(); document.execCommand('copy'); this.style.borderColor='#22c55e'; setTimeout(() => this.style.borderColor='#e5e7eb', 1000);">
                </div>

                {{-- Delete --}}
                <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb;">
                    <form action="{{ route('admin.media.destroy', $media) }}" method="POST" onsubmit="return confirm('Permanently delete this file? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">🗑️ Delete Media</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection