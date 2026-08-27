@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- ═══ STAT CARDS ═══ --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📚</div>
        <div class="stat-value">{{ $stats['subjects'] }}</div>
        <div class="stat-label">Subjects <span style="font-size:11px;color:#22c55e;">({{ $stats['subjects_published'] }} live)</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📂</div>
        <div class="stat-value">{{ $stats['topics'] }}</div>
        <div class="stat-label">Topics <span style="font-size:11px;color:#22c55e;">({{ $stats['topics_published'] }} live)</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-value">{{ $stats['lessons'] }}</div>
        <div class="stat-label">Total Lessons</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-value">{{ $stats['lessons_published'] }}</div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-value">{{ $stats['lessons_in_review'] }}</div>
        <div class="stat-label">In Review</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-value">{{ $stats['lessons_draft'] }}</div>
        <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🎯</div>
        <div class="stat-value">{{ $stats['quizzes'] }}</div>
        <div class="stat-label">Quizzes <span style="font-size:11px;color:#6b7280;">({{ $stats['questions'] }} Qs)</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">❓</div>
        <div class="stat-value">{{ $stats['quiz_types'] }}</div>
        <div class="stat-label">Active Types</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🖼️</div>
        <div class="stat-value">{{ $stats['media_items'] }}</div>
        <div class="stat-label">Media Items</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-value">{{ $stats['admins'] }}</div>
        <div class="stat-label">Admin Users</div>
    </div>
</div>

{{-- ═══ TWO COLUMN: Pipeline + Coverage Gaps ═══ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:8px;">

    {{-- Publishing Pipeline --}}
    <div class="card">
        <div class="card-header"><h3>🔄 Publishing Pipeline</h3></div>
        <div class="card-body">
            @php $totalLessons = $pipeline['draft'] + $pipeline['in_review'] + $pipeline['published'] + $pipeline['archived']; @endphp
            @if ($totalLessons > 0)
                <div style="display:flex;height:32px;border-radius:8px;overflow:hidden;margin-bottom:16px;">
                    @if ($pipeline['draft'] > 0)
                        <div style="width:{{ ($pipeline['draft']/$totalLessons)*100 }}%;background:#6b7280;" title="Draft: {{ $pipeline['draft'] }}"></div>
                    @endif
                    @if ($pipeline['in_review'] > 0)
                        <div style="width:{{ ($pipeline['in_review']/$totalLessons)*100 }}%;background:#f59e0b;" title="In Review: {{ $pipeline['in_review'] }}"></div>
                    @endif
                    @if ($pipeline['published'] > 0)
                        <div style="width:{{ ($pipeline['published']/$totalLessons)*100 }}%;background:#22c55e;" title="Published: {{ $pipeline['published'] }}"></div>
                    @endif
                    @if ($pipeline['archived'] > 0)
                        <div style="width:{{ ($pipeline['archived']/$totalLessons)*100 }}%;background:#94a3b8;" title="Archived: {{ $pipeline['archived'] }}"></div>
                    @endif
                </div>
                <div style="display:flex;justify-content:space-around;font-size:13px;text-align:center;">
                    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#6b7280;margin-right:4px;"></span> Draft: <strong>{{ $pipeline['draft'] }}</strong></div>
                    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f59e0b;margin-right:4px;"></span> Review: <strong>{{ $pipeline['in_review'] }}</strong></div>
                    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#22c55e;margin-right:4px;"></span> Live: <strong>{{ $pipeline['published'] }}</strong></div>
                    <div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#94a3b8;margin-right:4px;"></span> Archive: <strong>{{ $pipeline['archived'] }}</strong></div>
                </div>
            @else
                <p style="color:#999;text-align:center;padding:16px;">No lessons yet. Create one to see the pipeline.</p>
            @endif
        </div>
    </div>

    {{-- Coverage Gaps --}}
    <div class="card">
        <div class="card-header"><h3>🔍 Coverage Gaps</h3></div>
        <div class="card-body">
            <div style="display:grid;gap:10px;">
                @php
                    $gaps = [
                        ['Subjects without topics', $coverage['subjects_without_topics'], '📚', url('/admin/subjects')],
                        ['Topics without lessons', $coverage['topics_without_lessons'], '📂', url('/admin/topics')],
                        ['Lessons missing video', $coverage['lessons_without_video'], '🎬', url('/admin/lessons')],
                        ['Lessons missing quiz', $coverage['lessons_without_quiz'], '🎯', url('/admin/lessons')],
                    ];
                @endphp
                @foreach ($gaps as [$label, $count, $icon, $url])
                    <a href="{{ $url }}" style="display:flex;justify-content:space-between;align-items:center;padding:10px 14px;border-radius:8px;text-decoration:none;{{ $count > 0 ? 'background:#fef3c7;' : 'background:#f0fdf4;' }}">
                        <span style="font-size:14px;color:#374151;">{{ $icon }} {{ $label }}</span>
                        <span style="font-weight:bold;font-size:16px;{{ $count > 0 ? 'color:#d97706;' : 'color:#22c55e;' }}">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ═══ SUBJECT COVERAGE TABLE ═══ --}}
@if ($subjectCoverage->isNotEmpty())
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h3>📊 Subject Coverage</h3>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-secondary" style="font-size:12px;">Manage →</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr><th>Subject</th><th>Topics</th><th>Lessons</th><th>Publish Rate</th><th>Bar</th></tr>
            </thead>
            <tbody>
                @foreach ($subjectCoverage as $subject)
                    <tr>
                        <td><strong>{{ $subject->icon }} {{ $subject->name }}</strong></td>
                        <td>{{ $subject->topics_count }}</td>
                        <td>{{ $subject->lessons_count }}</td>
                        <td>
                            <span style="font-weight:bold;color:{{ $subject->publish_rate >= 70 ? '#22c55e' : ($subject->publish_rate >= 40 ? '#f59e0b' : '#ef4444') }};">
                                {{ $subject->publish_rate }}%
                            </span>
                        </td>
                        <td style="width:200px;">
                            <div style="background:#e5e7eb;border-radius:4px;height:10px;overflow:hidden;">
                                <div style="width:{{ $subject->publish_rate }}%;height:100%;background:{{ $subject->publish_rate >= 70 ? '#22c55e' : ($subject->publish_rate >= 40 ? '#f59e0b' : '#ef4444') }};"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ═══ TWO COLUMN: Recent Lessons + Activity ═══ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-top:16px;">

    {{-- Recent Lessons --}}
    <div class="card">
        <div class="card-header">
            <h3>📝 Recent Lessons</h3>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary" style="font-size:12px;">View All →</a>
        </div>
        <div class="card-body">
            @if ($recentLessons->isEmpty())
                <p style="color:#999;text-align:center;padding:16px;">No lessons created yet.</p>
            @else
                <table class="table">
                    <thead><tr><th>Title</th><th>Subject</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                        @foreach ($recentLessons as $lesson)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.lessons.show', $lesson) }}" style="font-weight:600;color:#4f46e5;text-decoration:none;">{{ $lesson->title }}</a>
                                </td>
                                <td style="font-size:13px;">{{ $lesson->topic->subject->icon ?? '' }} {{ $lesson->topic->subject->name ?? '—' }}</td>
                                <td><span class="badge badge-{{ $lesson->status }}">{{ ucfirst(str_replace('_', ' ', $lesson->status)) }}</span></td>
                                <td style="font-size:12px;color:#999;">{{ $lesson->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="card">
        <div class="card-header"><h3>🔔 Recent Activity</h3></div>
        <div class="card-body">
            @if ($recentActivity->isEmpty())
                <p style="color:#999;text-align:center;padding:16px;">No activity yet.</p>
            @else
                <div style="display:grid;gap:10px;">
                    @foreach ($recentActivity as $log)
                        <div style="display:flex;gap:10px;align-items:flex-start;padding:8px;border-radius:8px;background:#f8fafc;">
                            <span style="font-size:16px;">{{ $log->icon }}</span>
                            <div style="font-size:12px;flex:1;">
                                <strong>{{ $log->admin?->name ?? 'System' }}</strong>
                                {{ ucfirst($log->action) }}
                                @if ($log->from_status && $log->to_status)
                                    <br><span style="color:#999;">{{ str_replace('_', ' ', ucfirst($log->from_status)) }} → {{ str_replace('_', ' ', ucfirst($log->to_status)) }}</span>
                                @endif
                                <br><span style="color:#aaa;">{{ $log->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══ QUICK ACTIONS ═══ --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h3>⚡ Quick Actions</h3></div>
    <div class="card-body">
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary">📚 New Subject</a>
            <a href="{{ route('admin.topics.create') }}" class="btn btn-primary">📂 New Topic</a>
            <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">📝 New Lesson</a>
            <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">🎯 New Quiz</a>
            <a href="{{ route('admin.media.create') }}" class="btn btn-primary">🖼️ Upload Media</a>
        </div>
    </div>
</div>

@endsection