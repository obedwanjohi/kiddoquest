@extends('admin.layouts.app')
@section('title', 'Media Library')
@section('content')

{{-- Filter Bar --}}
<div class="card">
    <div class="card-body" style="padding:16px;">
        <form method="GET" action="{{ route('admin.media.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
            <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                <label class="form-label" style="font-size:12px;">🔍 Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by name…" value="{{ request('search') }}">
            </div>
            <div class="form-group" style="margin:0;min-width:140px;">
                <label class="form-label" style="font-size:12px;">Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="image" @selected(request('type') === 'image')>🖼️ Images</option>
                    <option value="video" @selected(request('type') === 'video')>🎬 Videos</option>
                    <option value="audio" @selected(request('type') === 'audio')>🔊 Audio</option>
                    <option value="document" @selected(request('type') === 'document')>📄 Documents</option>
                </select>
            </div>
            <div class="form-group" style="margin:0;min-width:160px;">
                <label class="form-label" style="font-size:12px;">Subject</label>
                <select name="subject_id" class="form-control">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) request('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.media.index') }}" class="btn btn-secondary">Clear</a>
        </form>
    </div>
</div>

{{-- Media Grid --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h3>🖼️ Media Library</h3>
        <a href="{{ route('admin.media.create') }}" class="btn btn-primary">+ Upload Media</a>
    </div>
    <div class="card-body">
        @if ($mediaItems->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🖼️</div>
                <h3>No media found</h3>
                <p>Upload images, videos, and audio files to use in lessons and quizzes.</p>
                <a href="{{ route('admin.media.create') }}" class="btn btn-primary">Upload Media</a>
            </div>
        @else
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
                @foreach ($mediaItems as $media)
                    <div style="border:2px solid #e5e7eb;border-radius:12px;overflow:hidden;background:white;position:relative;">
                        <a href="{{ route('admin.media.show', $media) }}" style="text-decoration:none;color:inherit;">
                            {{-- Thumbnail / Preview --}}
                            <div style="height:140px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;overflow:hidden;">
                                @if ($media->isImage())
                                    <img src="{{ $media->url }}" alt="{{ $media->name }}" style="width:100%;height:100%;object-fit:cover;">
                                @elseif ($media->isVideo())
                                    <span style="font-size:48px;">🎬</span>
                                @elseif ($media->isAudio())
                                    <span style="font-size:48px;">🔊</span>
                                @else
                                    <span style="font-size:48px;">📄</span>
                                @endif
                            </div>
                            {{-- Info --}}
                            <div style="padding:10px 12px;">
                                <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $media->name }}</div>
                                <div style="display:flex;justify-content:space-between;font-size:11px;color:#999;margin-top:4px;">
                                    <span>{{ strtoupper($media->extension) }}</span>
                                    <span>{{ $media->size_formatted }}</span>
                                </div>
                            </div>
                        </a>
                        {{-- Actions Bar --}}
                        <div style="padding:8px 12px;border-top:1px solid #f1f5f9;background:#fafafa;display:flex;justify-content:space-between;align-items:center;">
                            <a href="{{ route('admin.media.show', $media) }}" class="btn btn-sm btn-secondary" style="font-size:11px;padding:3px 8px;">👁️ View</a>
                            <a href="{{ route('admin.media.edit', $media) }}" class="btn btn-sm btn-secondary" style="font-size:11px;padding:3px 8px;">✏️ Edit</a>
                            <form action="{{ route('admin.media.destroy', $media) }}" method="POST" onsubmit="return confirm('Delete this media permanently?')" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" style="font-size:11px;padding:3px 8px;">🗑️</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div style="margin-top:24px;">
                {{ $mediaItems->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection