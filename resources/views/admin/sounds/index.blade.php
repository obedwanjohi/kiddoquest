@extends('admin.layouts.app')

@section('title', 'Game Sounds Management')

@section('content')
<div class="content-header" style="margin-bottom: 24px;">
    <h2>🎵 Game Sounds Management</h2>
    <p style="color: #64748b;">Upload WAV or MP3 sound effects to be played when kids answer questions correctly or incorrectly, or complete a mission.</p>
</div>

<div class="sounds-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 24px;">

    {{-- CORRECT SOUNDS --}}
    <div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="color: #10b981; margin-top: 0;">✅ Correct Answer Sounds ({{ count($correctSounds) }})</h3>
        
        <div class="upload-section" style="margin: 20px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="type" value="correct">
                <input type="file" name="sound" accept="audio/*, .mp3, .wav, .ogg, .m4a" required style="flex: 1;">
                <button type="submit" class="btn btn-primary" style="background: #10b981; border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: bold; cursor: pointer;">Upload Sound</button>
            </form>
        </div>

        <ul class="sound-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($correctSounds as $media)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #f1f5f9; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                        <span style="font-weight: bold; font-size: 13px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $media->file_name ?? $media->name }}</span>
                    </div>
                    <audio controls style="height: 30px; max-width: 180px;">
                        <source src="{{ $media->url }}" type="{{ $media->mime_type ?? 'audio/mpeg' }}">
                    </audio>
                    <form action="{{ route('admin.sounds.destroy') }}" method="POST" onsubmit="return confirm('Delete this sound?');" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $media->id }}">
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;">🗑️</button>
                    </form>
                </li>
            @empty
                <li style="padding: 12px; color: #64748b; text-align: center; font-style: italic;">No correct sounds uploaded yet. Falling back to default synthesizer.</li>
            @endforelse
        </ul>
    </div>

    {{-- WRONG SOUNDS --}}
    <div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="color: #ef4444; margin-top: 0;">❌ Wrong Answer Sounds ({{ count($wrongSounds) }})</h3>
        
        <div class="upload-section" style="margin: 20px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="type" value="wrong">
                <input type="file" name="sound" accept="audio/*, .mp3, .wav, .ogg, .m4a" required style="flex: 1;">
                <button type="submit" class="btn btn-primary" style="background: #ef4444; border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: bold; cursor: pointer;">Upload Sound</button>
            </form>
        </div>

        <ul class="sound-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($wrongSounds as $media)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #f1f5f9; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                        <span style="font-weight: bold; font-size: 13px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $media->file_name ?? $media->name }}</span>
                    </div>
                    <audio controls style="height: 30px; max-width: 180px;">
                        <source src="{{ $media->url }}" type="{{ $media->mime_type ?? 'audio/mpeg' }}">
                    </audio>
                    <form action="{{ route('admin.sounds.destroy') }}" method="POST" onsubmit="return confirm('Delete this sound?');" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $media->id }}">
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;">🗑️</button>
                    </form>
                </li>
            @empty
                <li style="padding: 12px; color: #64748b; text-align: center; font-style: italic;">No wrong sounds uploaded yet. Falling back to default synthesizer.</li>
            @endforelse
        </ul>
    </div>

    {{-- CELEBRATION SOUNDS --}}
    <div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="color: #f59e0b; margin-top: 0;">🎉 Celebration Fanfare ({{ count($celebSounds) }})</h3>
        
        <div class="upload-section" style="margin: 20px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                @csrf
                <input type="hidden" name="type" value="celebration">
                <input type="file" name="sound" accept="audio/*, .mp3, .wav, .ogg, .m4a" required style="flex: 1;">
                <button type="submit" class="btn btn-primary" style="background: #f59e0b; border: none; padding: 8px 16px; border-radius: 6px; color: white; font-weight: bold; cursor: pointer;">Upload Sound</button>
            </form>
        </div>

        <ul class="sound-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($celebSounds as $media)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #f1f5f9; gap: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;">
                        <span style="font-weight: bold; font-size: 13px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">{{ $media->file_name ?? $media->name }}</span>
                    </div>
                    <audio controls style="height: 30px; max-width: 180px;">
                        <source src="{{ $media->url }}" type="{{ $media->mime_type ?? 'audio/mpeg' }}">
                    </audio>
                    <form action="{{ route('admin.sounds.destroy') }}" method="POST" onsubmit="return confirm('Delete this sound?');" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="id" value="{{ $media->id }}">
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;">🗑️</button>
                    </form>
                </li>
            @empty
                <li style="padding: 12px; color: #64748b; text-align: center; font-style: italic;">No celebration sounds uploaded yet. Falling back to default synthesizer.</li>
            @endforelse
        </ul>
    </div>

</div>

@endsection
