@extends('admin.layouts.app')
@section('title', $lesson->title)
@section('content')

<div class="card">
    <div class="card-header">
        <h3>📝 {{ $lesson->title }}</h3>
        <div>
            <a href="{{ route('admin.lessons.preview', $lesson) }}" class="btn btn-primary" style="font-size:12px;" target="_blank">👁️ Preview as Child</a>
            <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">✏️ Edit</a>
            <a href="{{ route('admin.lessons.missions.index', $lesson) }}" class="btn btn-primary" style="font-size:12px;">🎯 Missions ({{ $lesson->missions->count() }})</a>
            <a href="{{ route('admin.lessons.index') }}" class="btn btn-secondary" style="font-size:12px;">← All Lessons</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">

            {{-- Left: Lesson Info --}}
            <div>
                {{-- Status Banner --}}
                <div style="padding:12px 16px;border-radius:8px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;background:{{ match($lesson->status) { 'draft' => '#f3f4f6', 'in_review' => '#fef3c7', 'published' => '#dcfce7', 'archived' => '#f1f5f9', default => '#f3f4f6' } }};border-left:4px solid {{ $lesson->status_color }};">
                    <div>
                        <span style="font-size:20px;">{{ match($lesson->status) { 'draft' => '⬜', 'in_review' => '🔍', 'published' => '✅', 'archived' => '📦', default => '⬜' } }}</span>
                        <strong style="margin-left:8px;">{{ ucfirst(str_replace('_', ' ', $lesson->status)) }}</strong>
                        @if ($lesson->isPublished && $lesson->published_at)
                            <span style="color:#666;font-size:13px;margin-left:8px;">since {{ $lesson->published_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                    <div style="font-size:12px;color:#999;">Version {{ $lesson->version }}</div>
                </div>

                @if ($lesson->rejection_reason)
                    <div style="padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:16px;">
                        <strong style="color:#dc2626;">❌ Rejection Reason:</strong>
                        <p style="margin:4px 0 0;color:#991b1b;">{{ $lesson->rejection_reason }}</p>
                    </div>
                @endif

                <table class="table" style="font-size:14px;">
                    <tr><td style="width:140px;color:#999;">Level</td><td>{{ $lesson->topic->subject->level->name ?? '—' }}</td></tr>
                    <tr><td style="color:#999;">Subject</td><td>{{ $lesson->topic->subject->icon ?? '' }} {{ $lesson->topic->subject->name ?? '—' }}</td></tr>
                    <tr><td style="color:#999;">Sub-Strand</td><td>@if($lesson->topic)<a href="{{ route('admin.topics.show', $lesson->topic) }}">{{ $lesson->topic->name }}</a>@else — @endif</td></tr>
                    <tr><td style="color:#999;">Type</td><td>{{ ucfirst($lesson->content_type) }}</td></tr>
                    <tr><td style="color:#999;">Duration</td><td>{{ $lesson->duration_minutes }} minutes</td></tr>
                    <tr><td style="color:#999;">Narration Voice</td><td>{{ $lesson->voice?->name ?? '— none —' }}</td></tr>
                    @if ($lesson->video_url)
                        <tr><td style="color:#999;">Video</td><td><a href="{{ $lesson->video_url }}" target="_blank" style="color:#4f46e5;">{{ $lesson->video_url }}</a></td></tr>
                    @endif
                    <tr><td style="color:#999;">Created By</td><td>{{ $lesson->creator?->name ?? '—' }}</td></tr>
                    @if ($lesson->reviewer)
                        <tr><td style="color:#999;">Reviewed By</td><td>{{ $lesson->reviewer->name }}</td></tr>
                    @endif
                </table>

                @if ($lesson->summary)
                    <h4 style="margin-top:20px;font-size:15px;">Short Description</h4>
                    <p style="color:#555;">{{ $lesson->summary }}</p>
                @endif

                @if ($lesson->learning_objective)
                    <h4 style="margin-top:20px;font-size:15px;">🎯 Learning Objective</h4>
                    <p style="color:#555;">{{ $lesson->learning_objective }}</p>
                @endif

                @if ($lesson->intro_narration_text || $lesson->summary_narration_text)
                    <h4 style="margin-top:20px;font-size:15px;">🎙️ Narration (dynamic)</h4>
                    @if ($lesson->intro_narration_text)
                        <p style="color:#999;font-size:12px;margin-bottom:2px;">Intro</p>
                        <div style="background:#f8fafc;padding:12px;border-radius:8px;white-space:pre-wrap;color:#444;margin-bottom:10px;">{{ $lesson->intro_narration_text }}</div>
                    @endif
                    @if ($lesson->summary_narration_text)
                        <p style="color:#999;font-size:12px;margin-bottom:2px;">Summary</p>
                        <div style="background:#f8fafc;padding:12px;border-radius:8px;white-space:pre-wrap;color:#444;">{{ $lesson->summary_narration_text }}</div>
                    @endif
                @endif

                @if ($lesson->content)
                    <h4 style="margin-top:20px;font-size:15px;">Content</h4>
                    <div style="background:#f8fafc;padding:16px;border-radius:8px;white-space:pre-wrap;color:#444;">{{ $lesson->content }}</div>
                @endif

                {{-- Workflow Actions --}}
                <div style="margin-top:24px;padding:16px;background:#f8fafc;border-radius:8px;">
                    <h4 style="font-size:14px;margin-bottom:12px;">🔄 Workflow Actions</h4>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        @if ($lesson->isDraft)
                            <form action="{{ route('admin.lessons.submit', $lesson) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">📤 Submit for Review</button>
                            </form>
                        @endif

                        @if ($lesson->isInReview)
                            <form action="{{ route('admin.lessons.approve', $lesson) }}" method="POST" style="display:flex;gap:4px;">
                                @csrf
                                <input type="text" name="review_notes" placeholder="Review notes (optional)" class="form-control" style="width:250px;font-size:13px;">
                                <button type="submit" class="btn btn-primary" style="background:#22c55e;">✅ Approve & Publish</button>
                            </form>
                            <button onclick="document.getElementById('reject-form').style.display='block'" class="btn btn-danger">❌ Reject</button>
                            <div id="reject-form" style="display:none;margin-top:8px;width:100%;">
                                <form action="{{ route('admin.lessons.reject', $lesson) }}" method="POST">
                                    @csrf
                                    <textarea name="rejection_reason" class="form-control" rows="2" placeholder="Explain why this is rejected (required)…" required style="margin-bottom:8px;"></textarea>
                                    <button type="submit" class="btn btn-danger">Send Back to Draft</button>
                                    <button type="button" onclick="document.getElementById('reject-form').style.display='none'" class="btn btn-secondary">Cancel</button>
                                </form>
                            </div>
                        @endif

                        @if (!$lesson->isArchived)
                            <form action="{{ route('admin.lessons.archive', $lesson) }}" method="POST" onsubmit="return confirm('Archive this lesson?')">
                                @csrf
                                <button type="submit" class="btn btn-secondary">📦 Archive</button>
                            </form>
                        @else
                            <form action="{{ route('admin.lessons.unarchive', $lesson) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">♻️ Restore</button>
                            </form>
                        @endif

                        <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Permanently delete this lesson?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Right: Audit Log Timeline --}}
            <div>
                <h4 style="font-size:14px;margin-bottom:12px;">📋 Audit History</h4>
                @if ($lesson->auditLogs->isEmpty())
                    <p style="color:#999;font-size:13px;">No activity recorded yet.</p>
                @else
                    <div style="position:relative;">
                        @foreach ($lesson->auditLogs as $log)
                            <div style="position:relative;padding-left:28px;padding-bottom:16px;{{ !$loop->last ? 'border-left:2px solid #e5e7eb;margin-left:9px;' : '' }}">
                                <div style="position:absolute;left:0;top:0;background:white;width:20px;height:20px;border-radius:50%;border:2px solid {{ $log->action === 'rejected' ? '#ef4444' : ($log->action === 'approved' || $log->action === 'published' ? '#22c55e' : '#c7d2fe') }};display:flex;align-items:center;justify-content:center;font-size:11px;">
                                    {{ $log->icon }}
                                </div>
                                <div style="font-size:12px;margin-left:8px;">
                                    <strong>{{ $log->admin?->name ?? 'System' }}</strong>
                                    <span style="color:#666;"> · {{ ucfirst($log->action) }}</span>
                                    @if ($log->from_status && $log->to_status)
                                        <br><span style="color:#999;font-size:11px;">{{ str_replace('_', ' ', ucfirst($log->from_status)) }} → {{ str_replace('_', ' ', ucfirst($log->to_status)) }}</span>
                                    @endif
                                    @if ($log->notes)
                                        <br><span style="color:#666;font-style:italic;">"{{ \Illuminate\Support\Str::limit($log->notes, 100) }}"</span>
                                    @endif
                                    <br><span style="color:#aaa;font-size:10px;">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- Missions Section --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h3>🎯 Missions ({{ $lesson->missions->count() }})</h3>
        <a href="{{ route('admin.lessons.missions.create', $lesson) }}" class="btn btn-primary" style="font-size:12px;">➕ Add Mission</a>
    </div>
    <div class="card-body">
        @if ($lesson->missions->isNotEmpty())
            <div style="display:grid;gap:10px;">
                @foreach ($lesson->missions as $mission)
                    <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f8fafc;border-radius:8px;border-left:4px solid {{ $mission->status_color }};">
                        @if ($mission->thumbnailMedia)
                            <img src="{{ $mission->thumbnailMedia->url }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;">
                        @else
                            <span style="font-size:32px;">🎯</span>
                        @endif
                        <div style="flex:1;">
                            <div style="font-weight:700;font-size:15px;">{{ $mission->title }}</div>
                            <div style="font-size:12px;color:#64748b;display:flex;gap:12px;flex-wrap:wrap;margin-top:2px;">
                                <span class="badge badge-{{ $mission->status }}">{{ ucfirst(str_replace('_', ' ', $mission->status)) }}</span>
                                @if ($mission->questionBank)<span>📋 {{ $mission->questionBank->name }}</span>@endif
                                <span>⭐ {{ $mission->stars_reward }}</span>
                                @if ($mission->video_url || $mission->videoMedia)<span>🎬 Video</span>@endif
                                @if ($mission->intro_narration_text)<span>🎙️ Intro</span>@endif
                            </div>
                        </div>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.lessons.missions.show', [$lesson, $mission]) }}" class="btn btn-secondary" style="font-size:11px;">👁</a>
                            <a href="{{ route('admin.lessons.missions.edit', [$lesson, $mission]) }}" class="btn btn-secondary" style="font-size:11px;">✏</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:30px;">
                <div style="font-size:48px;">🎯</div>
                <h3 style="margin:8px 0;">No Missions Yet</h3>
                <p style="color:#64748b;margin-bottom:16px;">Missions are where the child actually learns — intro narration, video, questions, and stars.</p>
                <a href="{{ route('admin.lessons.missions.create', $lesson) }}" class="btn btn-primary">➕ Create First Mission</a>
            </div>
        @endif
    </div>
</div>
@endsection
