@extends('admin.layouts.app')
@section('title', 'Curricula')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎓 Curricula</h3>
        <div>
            @if ($trashedCount > 0)
                <a href="{{ route('admin.curricula.index', ['trashed' => $showTrashed ? null : 1]) }}" class="btn btn-secondary">
                    {{ $showTrashed ? '← Active' : "🗑️ Trash ($trashedCount)" }}
                </a>
            @endif
            @unless ($showTrashed)
                <a href="{{ route('admin.curricula.create') }}" class="btn btn-primary">+ New Curriculum</a>
            @endunless
        </div>
    </div>
    <div class="card-body">
        @if ($curricula->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🎓</div>
                <h3>{{ $showTrashed ? 'Trash is empty' : 'No curricula yet' }}</h3>
                @unless ($showTrashed)
                    <p>Create your first curriculum (e.g. CBC) to start building the hierarchy.</p>
                    <a href="{{ route('admin.curricula.create') }}" class="btn btn-primary">Create Curriculum</a>
                @endunless
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Slug</th>
                        <th>Levels</th>
                        <th>Sort</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($curricula as $curriculum)
                        <tr>
                            <td style="font-size:20px;">{{ $curriculum->icon }}</td>
                            <td>
                                <strong>{{ $curriculum->name }}</strong>
                                @if ($curriculum->description)
                                    <br><small style="color:#a0aec0;">{{ Str::limit($curriculum->description, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $curriculum->code ?: '—' }}</td>
                            <td><code>{{ $curriculum->slug }}</code></td>
                            <td>
                                <span class="badge badge-draft">{{ $curriculum->levels_count }} level{{ $curriculum->levels_count !== 1 ? 's' : '' }}</span>
                            </td>
                            <td>{{ $curriculum->sort_order }}</td>
                            <td><span class="badge badge-{{ $curriculum->status }}">{{ ucfirst($curriculum->status) }}</span></td>
                            <td>
                                @if ($showTrashed)
                                    <form action="{{ route('admin.curricula.restore', $curriculum->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('admin.curricula.force-delete', $curriculum->id) }}" method="POST" style="display:inline;"
                                          onsubmit="return confirm('Permanently delete this curriculum? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Delete Forever</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.curricula.show', $curriculum) }}" class="btn btn-secondary" style="font-size:12px;">View</a>
                                    <a href="{{ route('admin.curricula.edit', $curriculum) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
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
