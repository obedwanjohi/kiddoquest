@extends('admin.layouts.app')
@section('title', 'Missions')
@section('content')

<div class="card">
    <div class="card-header">
        <h3>🎯 Missions</h3>
        <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary" style="font-size:12px;">📝 Pick a Lesson to Add Mission</a>
    </div>
    <div class="card-body" style="padding:12px 20px;">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.missions.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin-bottom:16px;">
            <div>
                <label style="font-size:11px;color:#94a3b8;display:block;">Level</label>
                <select name="level_id" class="form-control" style="font-size:13px;width:auto;" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#94a3b8;display:block;">Subject</label>
                <select name="subject_id" class="form-control" style="font-size:13px;width:auto;" onchange="this.form.submit()">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#94a3b8;display:block;">Lesson</label>
                <select name="lesson_id" class="form-control" style="font-size:13px;width:auto;" onchange="this.form.submit()">
                    <option value="">All Lessons</option>
                    @foreach ($lessons as $lessonItem)
                        <option value="{{ $lessonItem->id }}" {{ request('lesson_id') == $lessonItem->id ? 'selected' : '' }}>{{ $lessonItem->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:11px;color:#94a3b8;display:block;">Status</label>
                <select name="status" class="form-control" style="font-size:13px;width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In Review</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                </select>
            </div>
            @if (request()->hasAny(['level_id', 'subject_id', 'lesson_id', 'status']))
                <a href="{{ route('admin.missions.index') }}" class="btn btn-secondary" style="font-size:12px;">✕ Clear</a>
            @endif
        </form>

        <div style="font-size:13px;color:#64748b;margin-bottom:8px;">Showing {{ $missions->total() }} mission(s)</div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success" style="margin-top:16px;">{{ session('success') }}</div>
@endif

@if ($missions->isNotEmpty())
    <div style="display:grid;gap:12px;margin-top:16px;">
        @foreach ($missions as $mission)
            <div class="card" style="border-left:4px solid {{ $mission->status_color }};">
                <div class="card-header">
                    <h4>
                        @if ($mission->thumbnailMedia)
                            <img src="{{ $mission->thumbnailMedia->url }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover;vertical-align:middle;margin-right:8px;">
                        @else
                            <span style="font-size:28px;vertical-align:middle;">🎯</span>
                        @endif
                        {{ $mission->title }}
                        <span class="badge badge-{{ $mission->status }}" style="margin-left:8px;">{{ ucfirst(str_replace('_', ' ', $mission->status)) }}</span>
                    </h4>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('admin.lessons.missions.show', [$mission->lesson, $mission]) }}" class="btn btn-secondary" style="font-size:12px;">👁 View</a>
                        <a href="{{ route('admin.lessons.missions.edit', [$mission->lesson, $mission]) }}" class="btn btn-secondary" style="font-size:12px;">✏ Edit</a>
                    </div>
                </div>
                <div class="card-body" style="padding:10px 16px;">
                    <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;color:#64748b;">
                        <span><strong>📝 Lesson:</strong> <a href="{{ route('admin.lessons.show', $mission->lesson) }}">{{ $mission->lesson->title }}</a></span>
                        @if ($mission->lesson && $mission->lesson->topic)
                            <span><strong>📚 Subject:</strong> {{ $mission->lesson->topic->subject->name ?? '—' }}</span>
                        @endif
                        @if ($mission->questionBank)
                            <span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:10px;">📋 {{ $mission->questionBank->name }}</span>
                        @endif
                        <span>⭐ {{ $mission->stars_reward }}</span>
                        <span>📊 {{ $mission->pass_threshold_percent }}%</span>
                        @if ($mission->video_url || $mission->videoMedia)
                            <span>🎬 Video ✓</span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div style="margin-top:20px;">
        {{ $missions->links() }}
    </div>
@else
    <div class="card" style="margin-top:16px;">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">🎯</div>
                <h3>No missions yet</h3>
                <p>Missions are created inside lessons. Open a lesson to add your first mission.</p>
                <a href="{{ route('admin.lessons.index') }}" class="btn btn-primary">📝 Browse Lessons</a>
            </div>
        </div>
    </div>
@endif
@endsection