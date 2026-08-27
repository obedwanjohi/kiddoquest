@extends('admin.layouts.app')
@section('title', 'Edit Lesson')
@section('content')
@php $fromSub = request('return_to') === 'subStrand'; $backUrl = $fromSub && $lesson->topic ? route('admin.topics.show', $lesson->topic) : route('admin.lessons.index'); @endphp
<div class="card">
    <div class="card-header">
        <h3>📝 Edit Lesson @if($lesson->topic)<small style="color:#a0aec0;">in {{ $lesson->topic->name }}</small>@endif</h3>
        <div>
            <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-secondary">View</a>
            <a href="{{ route('admin.lessons.preview', $lesson) }}" class="btn btn-secondary" target="_blank">Preview</a>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.lessons.update', array_merge([$lesson], $fromSub ? ['return_to' => 'subStrand'] : [])) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.lessons._form', ['lesson' => $lesson])
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="{{ $backUrl }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top:20px; border-color:#fed7d7;">
    <div class="card-header" style="background:#fff5f5;">
        <h3 style="color:#c53030;">⚠️ Danger Zone</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Delete this lesson?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete Lesson</button>
        </form>
    </div>
</div>
@endsection
