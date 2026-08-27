@extends('admin.layouts.app')
@section('title', 'New Curriculum')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎓 Create Curriculum</h3>
        <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.curricula.store') }}" method="POST">
            @csrf
            @include('admin.curricula._form', ['curriculum' => null])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Curriculum</button>
                <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
