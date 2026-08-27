@extends('admin.layouts.app')
@section('title', 'Edit Subject')
@section('content')
@php $fromLevel = request('return_to') === 'level'; $backUrl = $fromLevel && $subject->level ? route('admin.levels.show', $subject->level) : route('admin.subjects.index'); @endphp
<div class="card">
    <div class="card-header">
        <h3>{{ $subject->icon }} Edit Subject
            @if($subject->level)<small style="color:#a0aec0;">in {{ $subject->level->name }}</small>@endif
        </h3>
        <div>
            <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-secondary">View</a>
            <a href="{{ $backUrl }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        @include('admin.curricula._errors')
        <form action="{{ route('admin.subjects.update', array_merge([$subject], $fromLevel ? ['return_to' => 'level'] : [])) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.subjects._form', ['subject' => $subject])
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
        <p>Archiving this subject hides it but keeps its {{ $subject->topics()->count() }} topic(s). You can restore it later. Permanent deletion is only allowed once it has no topics.</p>
        <form action="{{ route('admin.subjects.destroy', array_merge([$subject], $fromLevel ? ['return_to' => 'level'] : [])) }}" method="POST" onsubmit="return confirm('Archive this subject?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Archive Subject</button>
        </form>
    </div>
</div>
@endsection
