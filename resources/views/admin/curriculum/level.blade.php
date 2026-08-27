@extends('admin.layouts.app')
@section('title', $level->name . ' — Curriculum')

@section('content')
<div class="page-header">
    <a href="{{ url('/admin/curriculum') }}" class="btn-back">← Curriculum</a>
    <h1>{{ $level->icon }} {{ $level->name }}</h1>
    <p class="text-muted">{{ $level->description }}</p>
    @if ($level->min_age && $level->max_age)
    <span class="tag">Ages {{ $level->min_age }}–{{ $level->max_age }}</span>
    @endif
    @if ($level->stage)<span class="tag">{{ $level->stage }}</span>@endif
</div>

@if ($level->subjects->isEmpty())
<div class="empty-state">
    <p>No subjects assigned to this level yet.</p>
</div>
@else
<table class="data-table">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Strands (Topics)</th>
            <th>Sub-Strands</th>
            <th>Lessons</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($level->subjects as $subject)
        <tr>
            <td>
                <a href="{{ url('/admin/curriculum/subject/' . $subject->id) }}" class="link-icon">
                    {{ $subject->icon }} {{ $subject->name }}
                </a>
            </td>
            <td>{{ $subject->topics_count }}</td>
            <td>{{ $subject->sub_strands_count }}</td>
            <td>{{ $subject->lessons_count }}</td>
            <td><span class="status-badge status-{{ $subject->status }}">{{ ucfirst($subject->status) }}</span></td>
            <td>
                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn-sm">Edit</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection