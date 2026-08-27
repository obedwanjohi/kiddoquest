@extends('admin.layouts.app')
@section('title', 'Edit Level')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $level->icon }} Edit Level</h3>
        <div>
            <a href="{{ route('admin.levels.show', $level) }}" class="btn btn-secondary">View</a>
            <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.levels.update', $level) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.levels._form', ['level' => $level])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top:20px; border-color:#fed7d7;">
    <div class="card-header" style="background:#fff5f5;">
        <h3 style="color:#c53030;">⚠️ Danger Zone</h3>
    </div>
    <div class="card-body">
        <p>Moving this level to trash hides it but keeps its {{ $level->subjects()->count() }} subject(s). You can restore it later. Permanent deletion is only allowed once it has no subjects.</p>
        <form action="{{ route('admin.levels.destroy', $level) }}" method="POST" onsubmit="return confirm('Move this level to trash?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Move to Trash</button>
        </form>
    </div>
</div>
@endsection
