@extends('admin.layouts.app')

@section('title', 'Game Sounds Management')

@section('content')
<div class="content-header" style="margin-bottom: 24px;">
    <h2>🎵 Game Sounds Management</h2>
    <p style="color: #64748b;">Upload MP3 sound effects to be played when kids answer questions correctly or incorrectly. The game selects one randomly from each category.</p>
</div>

<div class="sounds-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">

    {{-- CORRECT SOUNDS --}}
    <div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        <h3 style="color: #10b981; margin-top: 0;">✅ Correct Answer Sounds ({{ count($correctSounds) }}/5)</h3>
        
        <div class="upload-section" style="margin: 20px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                @csrf
                <input type="hidden" name="type" value="correct">
                <input type="file" name="sound" accept="audio/mpeg, .mp3" required style="flex: 1;" {{ count($correctSounds) >= 5 ? 'disabled' : '' }}>
                <button type="submit" class="btn btn-primary" style="background: #10b981;" {{ count($correctSounds) >= 5 ? 'disabled' : '' }}>Upload MP3</button>
            </form>
        </div>

        <ul class="sound-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($correctSounds as $sound)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: bold; width: 60px;">{{ $sound }}</span>
                        <audio controls style="height: 30px; max-width: 200px;">
                            <source src="{{ asset('sounds/correct/' . $sound) }}" type="audio/mpeg">
                        </audio>
                    </div>
                    <form action="{{ route('admin.sounds.destroy') }}" method="POST" onsubmit="return confirm('Delete this sound?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="correct">
                        <input type="hidden" name="filename" value="{{ $sound }}">
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
        <h3 style="color: #ef4444; margin-top: 0;">❌ Wrong Answer Sounds ({{ count($wrongSounds) }}/5)</h3>
        
        <div class="upload-section" style="margin: 20px 0; padding: 16px; background: #f8fafc; border-radius: 8px; border: 1px dashed #cbd5e1;">
            <form action="{{ route('admin.sounds.upload') }}" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                @csrf
                <input type="hidden" name="type" value="wrong">
                <input type="file" name="sound" accept="audio/mpeg, .mp3" required style="flex: 1;" {{ count($wrongSounds) >= 5 ? 'disabled' : '' }}>
                <button type="submit" class="btn btn-primary" style="background: #ef4444;" {{ count($wrongSounds) >= 5 ? 'disabled' : '' }}>Upload MP3</button>
            </form>
        </div>

        <ul class="sound-list" style="list-style: none; padding: 0; margin: 0;">
            @forelse($wrongSounds as $sound)
                <li style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-weight: bold; width: 60px;">{{ $sound }}</span>
                        <audio controls style="height: 30px; max-width: 200px;">
                            <source src="{{ asset('sounds/wrong/' . $sound) }}" type="audio/mpeg">
                        </audio>
                    </div>
                    <form action="{{ route('admin.sounds.destroy') }}" method="POST" onsubmit="return confirm('Delete this sound?');">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="type" value="wrong">
                        <input type="hidden" name="filename" value="{{ $sound }}">
                        <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.2rem;">🗑️</button>
                    </form>
                </li>
            @empty
                <li style="padding: 12px; color: #64748b; text-align: center; font-style: italic;">No wrong sounds uploaded yet. Falling back to default synthesizer.</li>
            @endforelse
        </ul>
    </div>

</div>

@endsection
