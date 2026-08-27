@extends('admin.layouts.app')
@section('title', 'Levels')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🪜 Levels</h3>
        <div>
            @if ($trashedCount > 0)
                <a href="{{ route('admin.levels.index', ['trashed' => $showTrashed ? null : 1]) }}" class="btn btn-secondary">
                    {{ $showTrashed ? '← Active' : "🗑️ Trash ($trashedCount)" }}
                </a>
            @endif
            @unless ($showTrashed)
                <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">+ New Level</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        @if ($levels->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🪜</div>
                <h3>{{ $showTrashed ? 'Trash is empty' : 'No levels yet' }}</h3>
                @unless ($showTrashed)
                    <p>Create your first level (e.g. Grade 1) under a curriculum.</p>
                    <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">Create Level</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Curriculum</th>
                        <th>Code</th>
                        <th>Ages</th>
                        <th>Subjects</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($levels as $level)
                        <tr>
                            <td style="font-size:20px;">{{ $level->icon }}</td>
                            <td>
                                <strong>{{ $level->name }}</strong>
                                @if ($level->stage)
                                    <br><small style="color:#a0aec0;">{{ str_replace('_', ' ', $level->stage) }}</small>
                                @endif
                            </td>
                            <td>{{ $level->curriculum->name ?? '—' }}</td>
                            <td>{{ $level->code ?: '—' }}</td>
                            <td>
                                @if ($level->min_age !== null || $level->max_age !== null)
                                    {{ $level->min_age }}–{{ $level->max_age }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="badge badge-draft">{{ $level->subjects_count }}</span></td>
                            <td>{{ $level->sort_order }}</td>
                            <td><span class="badge badge-{{ $level->status }}">{{ ucfirst($level->status) }}</span></td>
                            <td>
                                @if ($showTrashed)
                                    <form action="{{ route('admin.levels.restore', $level->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.levels.force-delete', $level->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Permanently delete this level? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Delete Forever</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.levels.show', $level) }}" class="btn btn-secondary" style="font-size:12px;">View</a>
                                    <a href="{{ route('admin.levels.edit', $level) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
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
