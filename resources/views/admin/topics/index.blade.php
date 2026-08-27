@extends('admin.layouts.app')
@section('title', 'Sub-Strands')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>📂 Sub-Strands</h3>
        <div>
            @if ($trashedCount > 0)
                <a href="{{ route('admin.topics.index', array_filter(['subject' => $subjectId, 'q' => $search ?: null, 'trashed' => $showTrashed ? null : 1])) }}" class="btn btn-secondary">
                    {{ $showTrashed ? '← Active' : "🗑️ Archived ($trashedCount)" }}
                </a>
            @endif
            @unless ($showTrashed)
                <a href="{{ route('admin.topics.create', array_filter(['subject_id' => $subjectId])) }}" class="btn btn-primary">+ New Sub-Strand</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        {{-- Filter + search --}}
        <form method="GET" action="{{ route('admin.topics.index') }}" style="margin-bottom:16px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
            @if ($showTrashed)<input type="hidden" name="trashed" value="1">@endif
            <label for="subject" style="margin:0; font-weight:600;">Subject:</label>
            <select id="subject" name="subject" class="form-control" style="max-width:260px;" onchange="this.form.submit()">
                <option value="">All subjects</option>
                @foreach ($subjects as $subj)
                    <option value="{{ $subj->id }}" @selected($subjectId === $subj->id)>{{ $subj->icon }} {{ $subj->name }}@if($subj->level) — {{ $subj->level->name }}@endif</option>
                @endforeach
            </select>
            <input type="text" name="q" class="form-control" style="max-width:220px;" placeholder="Search name…" value="{{ $search }}">
            <button type="submit" class="btn btn-secondary" style="font-size:13px;">Search</button>
            @if ($subjectId || $search !== '')
                <a href="{{ route('admin.topics.index', $showTrashed ? ['trashed' => 1] : []) }}" class="btn btn-secondary" style="font-size:12px;">Clear</a>
            @endif
        </form>

        @if ($topics->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📂</div>
                <h3>{{ $showTrashed ? 'Nothing archived' : ($search !== '' ? 'No matches' : 'No sub-strands yet') }}</h3>
                @unless ($showTrashed)
                    <p>Create a sub-strand under a subject (e.g. Numbers, Shapes, Patterns).</p>
                    <a href="{{ route('admin.topics.create', array_filter(['subject_id' => $subjectId])) }}" class="btn btn-primary">Create Sub-Strand</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Lessons</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topics as $topic)
                        <tr>
                            <td style="font-size:20px;">{{ $topic->icon }}</td>
                            <td>
                                <strong>{{ $topic->name }}</strong>
                                @if ($topic->description)
                                    <br><small style="color:#a0aec0;">{{ Str::limit($topic->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($topic->subject)
                                    <a href="{{ route('admin.subjects.show', $topic->subject) }}">{{ $topic->subject->icon }} {{ $topic->subject->name }}</a>
                                    @if ($topic->subject->level)<br><small style="color:#a0aec0;">{{ $topic->subject->level->name }}</small>@endif
                                @else
                                    <span style="color:#e53e3e;">⚠ none</span>
                                @endif
                            </td>
                            <td><span class="badge badge-draft">{{ $topic->lessons_count }} lesson{{ $topic->lessons_count !== 1 ? 's' : '' }}</span></td>
                            <td><span class="badge badge-{{ $topic->status }}">{{ ucfirst($topic->status) }}</span></td>
                            <td style="white-space:nowrap;">
                                @if ($showTrashed)
                                    <form action="{{ route('admin.topics.restore', $topic->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.topics.force-delete', $topic->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Permanently delete this sub-strand? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Delete Forever</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.topics.show', $topic) }}" class="btn btn-secondary" style="font-size:12px;">View</a>
                                    <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top:12px; color:#a0aec0; font-size:13px;">💡 To reorder sub-strands, open their Subject and use the ▲▼ buttons.</p>
        @endif
    </div>
</div>
@endsection
