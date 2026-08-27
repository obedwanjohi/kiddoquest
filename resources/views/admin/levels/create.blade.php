@extends('admin.layouts.app')
@section('title', 'New Level')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🪜 Create Level</h3>
        <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.levels.store') }}" method="POST">
            @csrf
            @include('admin.levels._form', ['level' => null])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Level</button>
                <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
