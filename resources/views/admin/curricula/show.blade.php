@extends('admin.layouts.app')
@section('title', $curriculum->name)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $curriculum->icon }} {{ $curriculum->name }}
            <span class="badge badge-{{ $curriculum->status }}">{{ ucfirst($curriculum->status) }}</span>
        </h3>
        <div>
            <a href="{{ route('admin.curricula.edit', $curriculum) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('admin.curricula.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        <table class="table">
            <tr><th style="width:180px;">Code</th><td>{{ $curriculum->code ?: '—' }}</td></tr>
            <tr><th>Slug</th><td><code>{{ $curriculum->slug }}</code></td></tr>
            <tr><th>Description</th><td>{{ $curriculum->description ?: '—' }}</td></tr>
            <tr><th>Color</th><td><span style="display:inline-block;width:16px;height:16px;border-radius:3px;vertical-align:middle;background:{{ $curriculum->color }};"></span> {{ $curriculum->color }}</td></tr>
            <tr><th>Sort Order</th><td>{{ $curriculum->sort_order }}</td></tr>
        </table>
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3>🪜 Levels ({{ $curriculum->levels->count() }})</h3>
        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">+ New Level</a>
    </div>
    <div class="card-body">
        @if ($curriculum->levels->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🪜</div>
                <h3>No levels yet</h3>
                <p>Add levels (e.g. Grade 1) under this curriculum.</p>
                <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">Create Level</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr><th style="width:70px;">Order</th><th>Icon</th><th>Name</th><th>Code</th><th>Subjects</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($curriculum->levels as $i => $level)
                        <tr>
                            <td style="white-space:nowrap;">
                                <form action="{{ route('admin.levels.move', $level) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === 0) title="Move up">▲</button>
                                </form>
                                <form action="{{ route('admin.levels.move', $level) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === $curriculum->levels->count() - 1) title="Move down">▼</button>
                                </form>
                            </td>
                            <td style="font-size:20px;">{{ $level->icon }}</td>
                            <td><a href="{{ route('admin.levels.show', $level) }}"><strong>{{ $level->name }}</strong></a></td>
                            <td>{{ $level->code ?: '—' }}</td>
                            <td><span class="badge badge-draft">{{ $level->subjects_count }}</span></td>
                            <td><span class="badge badge-{{ $level->status }}">{{ ucfirst($level->status) }}</span></td>
                            <td><a href="{{ route('admin.levels.edit', $level) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
