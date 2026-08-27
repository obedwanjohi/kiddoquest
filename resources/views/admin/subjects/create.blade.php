@extends('admin.layouts.app')
@section('title', 'New Subject')
@section('content')
@php $backLevel = request('return_to') === 'level' && $selectedLevelId ? $levels->firstWhere('id', $selectedLevelId) : null; @endphp
<div class="card">
    <div class="card-header">
        <h3>📚 Create Subject @if($backLevel)<small style="color:#a0aec0;">in {{ $backLevel->name }}</small>@endif</h3>
        <a href="{{ $backLevel ? route('admin.levels.show', $backLevel) : route('admin.subjects.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.subjects.store', request('return_to') === 'level' ? ['return_to' => 'level'] : []) }}" method="POST">
            @csrf
            @include('admin.subjects._form', ['subject' => null])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Subject</button>
                <a href="{{ $backLevel ? route('admin.levels.show', $backLevel) : route('admin.subjects.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
