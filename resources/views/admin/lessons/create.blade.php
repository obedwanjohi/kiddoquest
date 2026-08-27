@extends('admin.layouts.app')
@section('title', 'New Lesson')
@section('content')
@php $backTopic = request('return_to') === 'subStrand' && $selectedTopicId ? $topics->firstWhere('id', $selectedTopicId) : null; @endphp
<div class="card">
    <div class="card-header">
        <h3>📝 Create Lesson @if($backTopic)<small style="color:#a0aec0;">in {{ $backTopic->name }}</small>@endif</h3>
        <a href="{{ $backTopic ? route('admin.topics.show', $backTopic) : route('admin.lessons.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        @if ($topics->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📂</div>
                <h3>No sub-strands available</h3>
                <p>You need to create a sub-strand first before adding lessons.</p>
                <a href="{{ route('admin.topics.create') }}" class="btn btn-primary">Create Sub-Strand</a>
            </div>
        @else
            @include('admin.curricula._errors')
            <form action="{{ route('admin.lessons.store', request('return_to') === 'subStrand' ? ['return_to' => 'subStrand'] : []) }}" method="POST">
                @csrf
                @include('admin.lessons._form', ['lesson' => null])
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Lesson</button>
                    <a href="{{ $backTopic ? route('admin.topics.show', $backTopic) : route('admin.lessons.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
