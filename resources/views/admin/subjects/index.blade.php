@extends('admin.layouts.app')
@section('title', 'Subjects')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>📚 Subjects</h3>
        <div>
            @if ($trashedCount > 0)
                <a href="{{ route('admin.subjects.index', array_filter(['level' => $levelId, 'trashed' => $showTrashed ? null : 1])) }}" class="btn btn-secondary">
                    {{ $showTrashed ? '← Active' : "🗑️ Archived ($trashedCount)" }}
                </a>
            @endif
            @unless ($showTrashed)
                <a href="{{ route('admin.subjects.create', array_filter(['level_id' => $levelId])) }}" class="btn btn-primary">+ New Subject</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        {{-- Level filter --}}
        <form method="GET" action="{{ route('admin.subjects.index') }}" style="margin-bottom:16px; display:flex; gap:10px; align-items:center;">
            @if ($showTrashed)<input type="hidden" name="trashed" value="1">@endif
            <label for="level" style="margin:0; font-weight:600;">Filter by Level:</label>
            <select id="level" name="level" class="form-control" style="max-width:280px;" onchange="this.form.submit()">
                <option value="">All levels</option>
                @foreach ($levels as $lvl)
                    <option value="{{ $lvl->id }}" @selected($levelId === $lvl->id)>{{ $lvl->icon }} {{ $lvl->name }}</option>
                @endforeach
            </select>
            @if ($levelId)
                <a href="{{ route('admin.subjects.index', $showTrashed ? ['trashed' => 1] : []) }}" class="btn btn-secondary" style="font-size:12px;">Clear</a>
            @endif
        </form>

        @if ($subjects->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>{{ $showTrashed ? 'Nothing archived' : 'No subjects yet' }}</h3>
                @unless ($showTrashed)
                    <p>Create a subject under a level to start building the curriculum.</p>
                    <a href="{{ route('admin.subjects.create', array_filter(['level_id' => $levelId])) }}" class="btn btn-primary">Create Subject</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Slug</th>
                        <th>Sub-Strands</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $subject)
                        <tr>
                            <td style="font-size:20px;">{{ $subject->icon }}</td>
                            <td>
                                <strong>{{ $subject->name }}</strong>
                                @if ($subject->description)
                                    <br><small style="color:#a0aec0;">{{ Str::limit($subject->description, 60) }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($subject->level)
                                    <a href="{{ route('admin.levels.show', $subject->level) }}">{{ $subject->level->icon }} {{ $subject->level->name }}</a>
                                @else
                                    <span style="color:#e53e3e;">⚠ none</span>
                                @endif
                            </td>
                            <td><code>{{ $subject->slug }}</code></td>
                            <td><span class="badge badge-draft">{{ $subject->topics_count }}</span></td>
                            <td><span class="badge badge-{{ $subject->status }}">{{ ucfirst($subject->status) }}</span></td>
                            <td>
                                @if ($showTrashed)
                                    <form action="{{ route('admin.subjects.restore', $subject->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.subjects.force-delete', $subject->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Permanently delete this subject? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Delete Forever</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-secondary" style="font-size:12px;">View</a>
                                    <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top:12px; color:#a0aec0; font-size:13px;">💡 To reorder subjects, open their Level and use the ▲▼ buttons.</p>
        @endif
    </div>
</div>
@endsection
