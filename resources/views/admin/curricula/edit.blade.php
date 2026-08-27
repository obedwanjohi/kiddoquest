@extends('admin.layouts.app')
@section('title', 'Edit Curriculum')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $curriculum->icon }} Edit Curriculum</h3>
        <div>
            <a href="{{ route('admin.curricula.show', $curriculum) }}" class="btn btn-secondary">View</a>
            <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.curricula.update', $curriculum) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.curricula._form', ['curriculum' => $curriculum])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top:20px; border-color:#fed7d7;">
    <div class="card-header" style="background:#fff5f5;">
        <h3 style="color:#c53030;">⚠️ Danger Zone</h3>
    </div>
    <div class="card-body">
        <p>Moving this curriculum to trash hides it but keeps its {{ $curriculum->levels()->count() }} level(s). You can restore it later. Permanent deletion is only allowed once it has no levels.</p>
        <form action="{{ route('admin.curricula.destroy', $curriculum) }}" method="POST" onsubmit="return confirm('Move this curriculum to trash?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Move to Trash</button>
        </form>
    </div>
</div>
@endsection
