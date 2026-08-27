@extends('admin.layouts.app')
@section('title', 'New Voice')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎙️ Create Voice</h3>
        <a href="{{ route('admin.voices.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.voices.store') }}" method="POST">
            @csrf
            @include('admin.voices._form', ['voice' => null])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Voice</button>
                <a href="{{ route('admin.voices.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
