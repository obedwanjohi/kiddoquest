@extends('admin.layouts.app')
@section('title', $subject->name . ' — Curriculum')

@section('content')
<div class="page-header">
    <a href="{{ url('/admin/curriculum/level/' . ($subject->level_id ?? 1)) }}" class="btn-back">← {{ $subject->level->name ?? 'Level' }}</a>
    <h1>{{ $subject->icon }} {{ $subject->name }}</h1>
    <p class="text-muted">{{ $subject->description }}</p>
    @if ($subject->level)<span class="tag">{{ $subject->level->name }}</span>@endif
    <span class="status-badge status-{{ $subject->status }}">{{ ucfirst($subject->status) }}</span>
</div>

<div class="curriculum-tabs">
    <button class="tab-btn active" onclick="switchTab('sub-strands')">Sub-Strands</button>
    <button class="tab-btn" onclick="switchTab('strands')">Strands (Topics)</button>
    <button class="tab-btn" onclick="switchTab('lessons')">Lessons</button>
</div>

{{-- Sub-Strands Tab --}}
<div id="tab-sub-strands" class="tab-panel active">
    @if ($subject->subStrands->isEmpty())
    <div class="empty-state"><p>No sub-strands yet. Sub-strands are created under strands (topics).</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>Sub-Strand</th><th>Strand</th><th>Lessons</th><th>Code</th></tr></thead>
        <tbody>
            @foreach ($subject->subStrands as $sub)
            <tr>
                <td>{{ $sub->name }}</td>
                <td>{{ $sub->strand->name ?? '—' }}</td>
                <td>{{ $sub->lessons_count }}</td>
                <td>{{ $sub->code ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Strands Tab --}}
<div id="tab-strands" class="tab-panel">
    @if ($subject->topics->isEmpty())
    <div class="empty-state"><p>No strands (topics) yet.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>Strand (Topic)</th><th>Lessons</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach ($subject->topics as $topic)
            <tr>
                <td>{{ $topic->name }}</td>
                <td>{{ $topic->lessons->count() }}</td>
                <td><a href="{{ route('admin.topics.show', $topic) }}" class="btn-sm">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Lessons Tab --}}
<div id="tab-lessons" class="tab-panel">
    @if ($subject->lessons->isEmpty())
    <div class="empty-state"><p>No lessons yet.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>Lesson</th><th>Sub-Strand</th><th>Quiz Questions</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            @foreach ($subject->lessons as $lesson)
            <tr>
                <td>{{ $lesson->title }}</td>
                <td>{{ $lesson->subStrand->name ?? '—' }}</td>
                <td>{{ $lesson->quiz?->questions->count() ?? 0 }}</td>
                <td><span class="status-badge status-{{ $lesson->status }}">{{ ucfirst($lesson->status) }}</span></td>
                <td><a href="{{ route('admin.lessons.show', $lesson) }}" class="btn-sm">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.target.classList.add('active');
}
</script>
@endsection