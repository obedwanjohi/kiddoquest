@extends('admin.layouts.app')
@section('title', $level->name)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $level->icon }} {{ $level->name }}
            <span class="badge badge-{{ $level->status }}">{{ ucfirst($level->status) }}</span>
        </h3>
        <div>
            <a href="{{ route('admin.levels.edit', $level) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('admin.levels.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <tr><th style="width:180px;">Curriculum</th>
                <td>
                    @if ($level->curriculum)
                        <a href="{{ route('admin.curricula.show', $level->curriculum) }}">{{ $level->curriculum->icon }} {{ $level->curriculum->name }}</a>
                    @else
                        —
                    @endif
                </td>
            </tr>
            <tr><th>Code</th><td>{{ $level->code ?: '—' }}</td></tr>
            <tr><th>Slug</th><td><code>{{ $level->slug }}</code></td></tr>
            <tr><th>Stage</th><td>{{ $level->stage ? str_replace('_', ' ', $level->stage) : '—' }}</td></tr>
            <tr><th>Ages</th><td>{{ $level->min_age ?? '—' }}–{{ $level->max_age ?? '—' }}</td></tr>
            <tr><th>Description</th><td>{{ $level->description ?: '—' }}</td></tr>
        </table>
    </div>
</div>

{{-- Subject management hub for THIS level --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3>📚 Subjects in {{ $level->name }}
            <span class="badge badge-draft">{{ $subjects->count() }}</span>
        </h3>
        <div>
            @if ($archivedCount > 0)
                <a href="{{ route('admin.levels.show', [$level, 'archived' => $showArchived ? null : 1]) }}" class="btn btn-secondary">
                    {{ $showArchived ? '← Active' : "🗑️ Archived ($archivedCount)" }}
                </a>
            @endif
            @unless ($showArchived)
                <a href="{{ route('admin.subjects.create', ['level_id' => $level->id, 'return_to' => 'level']) }}" class="btn btn-primary">+ New Subject</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        @if ($subjects->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📚</div>
                <h3>{{ $showArchived ? 'Nothing archived' : 'No subjects yet' }}</h3>
                @unless ($showArchived)
                    <p>Add this level's first subject (e.g. Mathematics, English, CRE).</p>
                    <a href="{{ route('admin.subjects.create', ['level_id' => $level->id, 'return_to' => 'level']) }}" class="btn btn-primary">Create Subject</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        @unless ($showArchived)<th style="width:70px;">Order</th>@endunless
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Topics</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subjects as $i => $subject)
                        <tr>
                            @unless ($showArchived)
                                <td style="white-space:nowrap;">
                                    <form action="{{ route('admin.subjects.move', $subject) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === 0) title="Move up">▲</button>
                                    </form>
                                    <form action="{{ route('admin.subjects.move', $subject) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === $subjects->count() - 1) title="Move down">▼</button>
                                    </form>
                                </td>
                            @endunless
                            <td style="font-size:20px;">{{ $subject->icon }}</td>
                            <td>
                                <strong>{{ $subject->name }}</strong>
                                @if ($subject->description)
                                    <br><small style="color:#a0aec0;">{{ Str::limit($subject->description, 60) }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-draft">{{ $subject->topics_count }} sub-strand{{ $subject->topics_count !== 1 ? 's' : '' }}</span></td>
                            <td><span class="badge badge-{{ $subject->status }}">{{ ucfirst($subject->status) }}</span></td>
                            <td style="white-space:nowrap;">
                                @if ($showArchived)
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
                                    <a href="{{ route('admin.subjects.edit', [$subject, 'return_to' => 'level']) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                    <form action="{{ route('admin.subjects.destroy', [$subject, 'return_to' => 'level']) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Archive this subject?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Archive</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
