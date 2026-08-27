@extends('admin.layouts.app')
@section('title', $subject->name)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $subject->icon }} {{ $subject->name }}
            <span class="badge badge-{{ $subject->status }}">{{ ucfirst($subject->status) }}</span>
        </h3>
        <div>
            <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-primary">Edit</a>
            <a href="{{ $subject->level ? route('admin.levels.show', $subject->level) : route('admin.subjects.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
            <div>
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Level</p>
                <p>
                    @if ($subject->level)
                        <a href="{{ route('admin.levels.show', $subject->level) }}">{{ $subject->level->icon }} {{ $subject->level->name }}</a>
                        @if ($subject->level->curriculum)<small style="color:#a0aec0;"> · {{ $subject->level->curriculum->name }}</small>@endif
                    @else
                        <span style="color:#e53e3e;">⚠ none</span>
                    @endif
                </p>
            </div>
            <div>
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Slug</p>
                <p><code>{{ $subject->slug }}</code></p>
            </div>
        </div>
        @if ($subject->description)
            <div style="margin-top:16px;">
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Description</p>
                <p>{{ $subject->description }}</p>
            </div>
        @endif
    </div>
</div>

{{-- Sub-Strand management hub for THIS subject --}}
<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3>📂 Sub-Strands in {{ $subject->name }} <span class="badge badge-draft">{{ $subStrands->count() }}</span></h3>
        <div>
            @if ($archivedCount > 0)
                <a href="{{ route('admin.subjects.show', [$subject, 'archived' => $showArchived ? null : 1]) }}" class="btn btn-secondary">
                    {{ $showArchived ? '← Active' : "🗑️ Archived ($archivedCount)" }}
                </a>
            @endif
            @unless ($showArchived)
                <a href="{{ route('admin.topics.create', ['subject_id' => $subject->id, 'return_to' => 'subject']) }}" class="btn btn-primary">+ New Sub-Strand</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        {{-- Search within this subject --}}
        <form method="GET" action="{{ route('admin.subjects.show', $subject) }}" style="margin-bottom:16px; display:flex; gap:10px; align-items:center;">
            @if ($showArchived)<input type="hidden" name="archived" value="1">@endif
            <input type="text" name="q" class="form-control" style="max-width:260px;" placeholder="Search sub-strands…" value="{{ $search }}">
            <button type="submit" class="btn btn-secondary" style="font-size:13px;">Search</button>
            @if ($search !== '')
                <a href="{{ route('admin.subjects.show', array_merge([$subject], $showArchived ? ['archived' => 1] : [])) }}" class="btn btn-secondary" style="font-size:12px;">Clear</a>
            @endif
        </form>

        @if ($subStrands->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📂</div>
                <h3>{{ $showArchived ? 'Nothing archived' : ($search !== '' ? 'No matches' : 'No sub-strands yet') }}</h3>
                @unless ($showArchived)
                    <p>Add this subject's first sub-strand (e.g. Numbers, Shapes, Patterns, Measurement).</p>
                    <a href="{{ route('admin.topics.create', ['subject_id' => $subject->id, 'return_to' => 'subject']) }}" class="btn btn-primary">Create Sub-Strand</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        @unless ($showArchived)<th style="width:70px;">Order</th>@endunless
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Lessons</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subStrands as $i => $ss)
                        <tr>
                            @unless ($showArchived)
                                <td style="white-space:nowrap;">
                                    <form action="{{ route('admin.topics.move', $ss) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === 0) title="Move up">▲</button>
                                    </form>
                                    <form action="{{ route('admin.topics.move', $ss) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === $subStrands->count() - 1) title="Move down">▼</button>
                                    </form>
                                </td>
                            @endunless
                            <td style="font-size:20px;">{{ $ss->icon }}</td>
                            <td>
                                <a href="{{ route('admin.topics.show', $ss) }}"><strong>{{ $ss->name }}</strong></a>
                                @if ($ss->description)
                                    <br><small style="color:#a0aec0;">{{ Str::limit($ss->description, 60) }}</small>
                                @endif
                            </td>
                            <td><span class="badge badge-draft">{{ $ss->lessons_count }} lesson{{ $ss->lessons_count !== 1 ? 's' : '' }}</span></td>
                            <td><span class="badge badge-{{ $ss->status }}">{{ ucfirst($ss->status) }}</span></td>
                            <td style="white-space:nowrap;">
                                @if ($showArchived)
                                    <form action="{{ route('admin.topics.restore', $ss->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.topics.force-delete', $ss->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Permanently delete this sub-strand? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Delete Forever</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.topics.edit', [$ss, 'return_to' => 'subject']) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                    <form action="{{ route('admin.topics.destroy', [$ss, 'return_to' => 'subject']) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Archive this sub-strand?');">
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
