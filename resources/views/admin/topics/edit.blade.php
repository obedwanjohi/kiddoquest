@extends('admin.layouts.app')
@section('title', 'Edit Sub-Strand')
@section('content')
@php $fromSubject = request('return_to') === 'subject'; $backUrl = $fromSubject && $topic->subject ? route('admin.subjects.show', $topic->subject) : route('admin.topics.index'); @endphp
<div class="card">
    <div class="card-header">
        <h3>{{ $topic->icon }} Edit Sub-Strand
            @if($topic->subject)<small style="color:#a0aec0;">in {{ $topic->subject->name }}</small>@endif
        </h3>
        <div>
            <a href="{{ route('admin.topics.show', $topic) }}" class="btn btn-secondary">View</a>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.topics.update', array_merge([$topic], $fromSubject ? ['return_to' => 'subject'] : [])) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.topics._form', ['topic' => $topic])
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
        <p>Archiving this sub-strand hides it but keeps its {{ $topic->lessons()->count() }} lesson(s). You can restore it later. Permanent deletion is only allowed once it has no lessons.</p>
        <form action="{{ route('admin.topics.destroy', array_merge([$topic], $fromSubject ? ['return_to' => 'subject'] : [])) }}" method="POST" onsubmit="return confirm('Archive this sub-strand?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Archive Sub-Strand</button>
        </form>
    </div>
</div>
@endsection
