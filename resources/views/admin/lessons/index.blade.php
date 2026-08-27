@extends('admin.layouts.app')
@section('title', 'Lessons')
@section('content')

{{-- Status Filter Tabs --}}
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
    <a href="{{ route('admin.lessons.index') }}"
       style="padding:6px 16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;{{ !request('status') ? 'background:#4f46e5;color:white;' : 'background:white;color:#4f46e5;border:1px solid #c7d2fe;' }}">
        All ({{ $statusCounts['all'] }})
    </a>
    @foreach (['draft' => '⬜', 'in_review' => '🔍', 'published' => '✅', 'archived' => '📦'] as $status => $icon)
        <a href="{{ route('admin.lessons.index', ['status' => $status]) }}"
           style="padding:6px 16px;border-radius:8px;text-decoration:none;font-weight:600;font-size:13px;{{ request('status') === $status ? 'background:#4f46e5;color:white;' : 'background:white;color:#4f46e5;border:1px solid #c7d2fe;' }}">
            {{ $icon }} {{ ucfirst(str_replace('_', ' ', $status)) }} ({{ $statusCounts[$status] }})
        </a>
    @endforeach
</div>

{{-- Search + List --}}
<div class="card">
    <div class="card-header">
        <h3>📝 Lessons</h3>
        <div style="display:flex;gap:8px;">
            <form method="GET" action="{{ route('admin.lessons.index') }}" style="display:flex;gap:4px;">
                @if (request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <input type="text" name="search" class="form-control" placeholder="Search…" value="{{ request('search') }}" style="width:200px;font-size:13px;">
                <button type="submit" class="btn btn-secondary">🔍</button>
            </form>
            <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">+ New Lesson</a>
        </div>
    </div>
    <div class="card-body">
        @if ($lessons->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">📝</div>
                <h3>No lessons found</h3>
                <p>Create your first lesson — teach via movie, test via quiz.</p>
                <a href="{{ route('admin.lessons.create') }}" class="btn btn-primary">Create Lesson</a>
            </div>
        @else
            <form id="bulk-form" action="{{ route('admin.lessons.bulk') }}" method="POST">
                @csrf
                {{-- Bulk action bar --}}
                <div id="bulk-bar" style="display:none;background:#eef2ff;padding:10px 14px;border-radius:8px;margin-bottom:12px;align-items:center;justify-content:space-between;">
                    <span style="font-size:13px;font-weight:600;color:#4f46e5;"><span id="bulk-count">0</span> selected</span>
                    <div style="display:flex;gap:8px;">
                        <button type="submit" name="action" value="publish" class="btn btn-primary" style="font-size:12px;background:#22c55e;" onclick="return confirm('Publish selected lessons?')">✅ Publish</button>
                        <button type="submit" name="action" value="archive" class="btn btn-secondary" style="font-size:12px;" onclick="return confirm('Archive selected lessons?')">📦 Archive</button>
                        <button type="submit" name="action" value="delete" class="btn btn-danger" style="font-size:12px;" onclick="return confirm('PERMANENTLY DELETE selected lessons?')">🗑️ Delete</button>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="select-all" onclick="toggleAll(this)"></th>
                            <th>Title</th>
                            <th>Subject → Sub-Strand</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lessons as $lesson)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $lesson->id }}" class="bulk-check" onclick="updateBulkBar()"></td>
                                <td>
                                    <a href="{{ route('admin.lessons.show', $lesson) }}" style="font-weight:600;color:#4f46e5;text-decoration:none;">{{ $lesson->title }}</a>
                                    <div style="font-size:11px;color:#999;">⏱️ {{ $lesson->duration_minutes }}m · v{{ $lesson->version }}</div>
                                </td>
                                <td style="font-size:13px;">
                                    {{ $lesson->topic->subject->icon ?? '?' }} {{ $lesson->topic->subject->name ?? '—' }}
                                    <span style="color:#ccc;">→</span>
                                    {{ $lesson->topic->name ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge badge-{{ $lesson->status }}">{{ ucfirst(str_replace('_', ' ', $lesson->status)) }}</span>
                                </td>
                                <td style="font-size:13px;color:#666;">{{ $lesson->creator?->name ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.lessons.show', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">View</a>
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>
            <div style="margin-top:16px;">{{ $lessons->links() }}</div>

            <script>
            function toggleAll(master) {
                document.querySelectorAll('.bulk-check').forEach(cb => cb.checked = master.checked);
                updateBulkBar();
            }
            function updateBulkBar() {
                const checked = document.querySelectorAll('.bulk-check:checked').length;
                document.getElementById('bulk-count').textContent = checked;
                document.getElementById('bulk-bar').style.display = checked > 0 ? 'flex' : 'none';
                const all = document.querySelectorAll('.bulk-check').length;
                document.getElementById('select-all').checked = checked === all && all > 0;
            }
            </script>
        @endif
    </div>
</div>
@endsection