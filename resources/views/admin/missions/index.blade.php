@extends('admin.layouts.app')
@section('title', 'Missions — ' . ($lesson->title ?? 'All Missions'))
@section('content')

<div class="card">
    <div class="card-header">
        <h3>🎯 Missions — {{ $lesson->title ?? 'Overview' }}</h3>
        <div>
            @if(isset($lesson) && $lesson)
                <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">← Lesson</a>
                <a href="{{ route('admin.lessons.missions.create', $lesson) }}" class="btn btn-primary" style="font-size:12px;">➕ New Mission</a>
            @endif
        </div>
    </div>
    <div class="card-body" style="padding:12px 20px;">
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:14px;color:#666;">
            <span><strong>📚 Subject:</strong> {{ $lesson->topic->subject->name ?? '—' }}</span>
            <span><strong>📖 Topic:</strong> {{ $lesson->topic->name ?? '—' }}</span>
            <span><strong>📊 Missions:</strong> {{ $lesson->missions->count() }}</span>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success" style="margin-top:16px;">{{ session('success') }}</div>
@endif

@if ($lesson->missions->isNotEmpty())
    <div style="display:grid;gap:12px;margin-top:16px;">
        @foreach ($lesson->missions as $mission)
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
                        @if ($mission->questionBank)
                            <span style="font-size:12px;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:10px;margin-left:4px;">📋 {{ $mission->questionBank->name }}</span>
                        @endif
                    </h4>
                    <div style="display:flex;gap:6px;">
                        <a href="{{ route('admin.lessons.missions.show', [$lesson, $mission]) }}" class="btn btn-secondary" style="font-size:12px;">👁 View</a>
                        <a href="{{ route('admin.lessons.missions.edit', [$lesson, $mission]) }}" class="btn btn-secondary" style="font-size:12px;">✏ Edit</a>
                        <form action="{{ route('admin.lessons.missions.duplicate', [$lesson, $mission]) }}" method="POST" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary" style="font-size:12px;">📋 Duplicate</button>
                        </form>
                        <form action="{{ route('admin.lessons.missions.destroy', [$lesson, $mission]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this mission?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="font-size:12px;">🗑</button>
                        </form>
                    </div>
                </div>
                <div class="card-body" style="padding:10px 16px;">
                    <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;color:#64748b;">
                        @if ($mission->intro_narration_text)
                            <span>🎙️ Intro: <em>"{{ Str::limit($mission->intro_narration_text, 60) }}"</em></span>
                        @endif
                        @if ($mission->video_url || $mission->videoMedia)
                            <span>🎬 Video: ✓</span>
                        @endif
                        <span>⭐ Stars: {{ $mission->stars_reward }}</span>
                        <span>📊 Pass: {{ $mission->pass_threshold_percent }}%</span>
                        <span>❓ Questions: {{ $mission->questions_per_session }}</span>
                        <span>⏱ {{ $mission->estimated_minutes }} min</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card" style="margin-top:16px;">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">🎯</div>
                <h3>No missions yet</h3>
                <p>Each lesson can have one or more missions (learning experiences).</p>
                <a href="{{ route('admin.lessons.missions.create', $lesson) }}" class="btn btn-primary">➕ Create First Mission</a>
            </div>
        </div>
    </div>
@endif
@endsection