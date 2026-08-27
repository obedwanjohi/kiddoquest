@extends('admin.layouts.app')
@section('title', 'New Sub-Strand')
@section('content')
@php $backSubject = request('return_to') === 'subject' && $selectedSubjectId ? $subjects->firstWhere('id', $selectedSubjectId) : null; @endphp
<div class="card">
    <div class="card-header">
        <h3>📂 Create Sub-Strand @if($backSubject)<small style="color:#a0aec0;">in {{ $backSubject->name }}</small>@endif</h3>
        <a href="{{ $backSubject ? route('admin.subjects.show', $backSubject) : route('admin.topics.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.topics.store', request('return_to') === 'subject' ? ['return_to' => 'subject'] : []) }}" method="POST">
            @csrf
            @include('admin.topics._form', ['topic' => null])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Sub-Strand</button>
                <a href="{{ $backSubject ? route('admin.subjects.show', $backSubject) : route('admin.topics.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
