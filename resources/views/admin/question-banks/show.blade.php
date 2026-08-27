@extends('admin.layouts.app')
@section('title', $questionBank->name . ' — Question Bank')

@section('content')
<div class="page-header">
    <a href="{{ route('admin.question-banks.index') }}" class="btn-back">← Question Banks</a>
    <div style="display:flex; justify-content:space-between; align-items:start; flex-wrap:wrap; gap:12px;">
        <div>
            <h1>🏦 {{ $questionBank->name }}</h1>
            @if ($questionBank->description)
            <p class="text-muted">{{ $questionBank->description }}</p>
            @endif
            <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                @if ($questionBank->lesson)<span class="tag">📖 {{ $questionBank->lesson->title }}</span>@endif
                @if ($questionBank->subject)<span class="tag">{{ $questionBank->subject->name }}</span>@endif
                @if ($questionBank->subStrand)<span class="tag">{{ $questionBank->subStrand->name }}</span>@endif
                @if ($questionBank->quizType)<span class="tag">{{ $questionBank->quizType->name }}</span>@endif
                @if ($questionBank->difficulty)<span class="badge difficulty-{{ $questionBank->difficulty }}">{{ ucfirst($questionBank->difficulty) }}</span>@endif
                <span class="status-badge status-{{ $questionBank->status }}">{{ ucfirst($questionBank->status) }}</span>
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('admin.question-banks.questions', $questionBank) }}" class="btn-sm btn-primary">📋 Manage Questions</a>
            <a href="{{ route('admin.question-banks.preview', $questionBank) }}" class="btn-sm">👁 Preview Draw</a>
            <form method="POST" action="{{ route('admin.question-banks.duplicate', $questionBank) }}" style="display:inline">
                @csrf
                <button type="submit" class="btn-sm">📋 Duplicate</button>
            </form>
            <a href="{{ route('admin.question-banks.edit', $questionBank) }}" class="btn-sm">✏️ Edit</a>
            <form method="POST" action="{{ route('admin.question-banks.destroy', $questionBank) }}" onsubmit="return confirm('Delete this bank? Questions will remain but be unlinked.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-sm btn-danger">🗑 Delete</button>
            </form>
        </div>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Bank Details --}}
<div class="details-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px; margin-bottom:30px;">
    <div class="detail-card">
        <div class="detail-label">Lesson</div>
        <div class="detail-value">{{ $questionBank->lesson?->title ?? '— Standalone —' }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Pool Size (per attempt)</div>
        <div class="detail-value">{{ $questionBank->pool_size }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Total Questions</div>
        <div class="detail-value">{{ $questionBank->pool_count }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Pass Threshold</div>
        <div class="detail-value">{{ $questionBank->pass_threshold }}%</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Max Attempts</div>
        <div class="detail-value">{{ $questionBank->max_attempts }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Randomize</div>
        <div class="detail-value">{{ $questionBank->shuffle ? '✅ Yes' : '⛔ No' }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Created By</div>
        <div class="detail-value">{{ $questionBank->creator?->name ?? 'System' }}</div>
    </div>
    <div class="detail-card">
        <div class="detail-label">Updated</div>
        <div class="detail-value">{{ $questionBank->updated_at?->diffForHumans() ?? '—' }}</div>
    </div>
</div>

<h2 style="display:flex; justify-content:space-between; align-items:center;">
    <span>Questions ({{ $questionBank->pool_count }})</span>
    <a href="{{ route('admin.question-banks.questions', $questionBank) }}" class="btn-sm btn-primary">📋 Manage Questions →</a>
</h2>

@php
    // Show assigned (M2M) pool if present, else legacy hasMany
    $poolQuestions = $questionBank->assignedQuestions->isNotEmpty() ? $questionBank->assignedQuestions : $questionBank->questions;
@endphp

@if ($poolQuestions->isEmpty())
<div class="empty-state">
    <p>No questions in this bank yet.</p>
    <a href="{{ route('admin.question-banks.questions', $questionBank) }}" class="btn-primary">📋 Assign Questions →</a>
</div>
@else
<div class="table-responsive">
<table class="data-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Question</th>
            <th>Type</th>
            <th>Options</th>
            <th>Used In</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($poolQuestions as $i => $q)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ Str::limit($q->prompt, 80) }}</td>
            <td>{{ $q->quizType?->name ?? '—' }}</td>
            <td>{{ $q->options->count() }}</td>
            <td>
                @if ($q->quiz)
                <a href="{{ route('admin.quizzes.show', $q->quiz) }}">{{ $q->quiz->title }}</a>
                @else
                <span class="text-muted">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>
@endif
@endsection
