@extends('admin.layouts.app')
@section('title', 'Edit Voice')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎙️ Edit Voice — {{ $voice->name }}</h3>
        <a href="{{ route('admin.voices.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.voices.update', $voice) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.voices._form', ['voice' => $voice])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.voices.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@if ($voice->lessons()->count() === 0)
<div class="card" style="margin-top:20px; border-color:#fed7d7;">
    <div class="card-header" style="background:#fff5f5;">
        <h3 style="color:#c53030;">⚠️ Danger Zone</h3>
    </div>
    <div class="card-body">
        <p>This voice is not used by any lesson and can be deleted.</p>
        <form action="{{ route('admin.voices.destroy', $voice) }}" method="POST" onsubmit="return confirm('Delete this voice?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Voice</button>
        </form>
    </div>
</div>
@else
<div class="card" style="margin-top:20px;">
    <div class="card-body">
        <p style="color:#a0aec0;">Used by {{ $voice->lessons()->count() }} lesson(s). Deactivate it instead of deleting to keep those lessons intact.</p>
    </div>
</div>
@endif
@endsection
