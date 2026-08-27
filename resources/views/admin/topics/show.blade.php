@extends('admin.layouts.app')
@section('title', $topic->name)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $topic->icon }} {{ $topic->name }}
            <span class="badge badge-{{ $topic->status }}">{{ ucfirst($topic->status) }}</span>
        </h3>
        <div>
            <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-primary">Edit</a>
            <a href="{{ $topic->subject ? route('admin.subjects.show', $topic->subject) : route('admin.topics.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:24px;">
            <div>
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Subject</p>
                <p>
                    @if ($topic->subject)
                        <a href="{{ route('admin.subjects.show', $topic->subject) }}">{{ $topic->subject->icon }} {{ $topic->subject->name }}</a>
                        @if ($topic->subject->level)<small style="color:#a0aec0;"> · {{ $topic->subject->level->name }}</small>@endif
                    @else — @endif
                </p>
            </div>
            <div>
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Slug</p>
                <p><code>{{ $topic->slug }}</code></p>
            </div>
        </div>
        @if ($topic->description)
            <div>
                <p style="color:#a0aec0; font-size:12px; text-transform:uppercase; font-weight:600;">Description</p>
                <p>{{ $topic->description }}</p>
            </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header">
        <h3>📝 Lessons in {{ $topic->name }} <span class="badge badge-draft">{{ $topic->lessons->count() }}</span></h3>
        <a href="{{ route('admin.lessons.create', ['topic_id' => $topic->id, 'return_to' => 'subStrand']) }}" class="btn btn-primary">+ New Lesson</a>
    </div>
    <div class="card-body">
        @if ($topic->lessons->isEmpty())
            <div class="empty-state" style="padding:30px;">
                <div class="empty-icon" style="font-size:36px;">📝</div>
                <h3>No lessons yet</h3>
                <p>Add this sub-strand's first lesson (e.g. "Let's Count with Leo").</p>
                <a href="{{ route('admin.lessons.create', ['topic_id' => $topic->id, 'return_to' => 'subStrand']) }}" class="btn btn-primary">Create Lesson</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr><th style="width:70px;">Order</th><th>Title</th><th>Status</th><th>Duration</th><th>Sort</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($topic->lessons as $i => $lesson)
                        <tr>
                            <td style="white-space:nowrap;">
                                <form action="{{ route('admin.lessons.move', $lesson) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === 0) title="Move up">▲</button>
                                </form>
                                <form action="{{ route('admin.lessons.move', $lesson) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit" class="btn btn-secondary" style="font-size:12px; padding:2px 8px;" @disabled($i === $topic->lessons->count() - 1) title="Move down">▼</button>
                                </form>
                            </td>
                            <td><a href="{{ route('admin.lessons.show', $lesson) }}"><strong>{{ $lesson->title }}</strong></a></td>
                            <td><span class="badge badge-{{ $lesson->status === 'in_review' ? 'review' : $lesson->status }}">{{ ucfirst(str_replace('_',' ',$lesson->status)) }}</span></td>
                            <td>{{ $lesson->duration_minutes }} min</td>
                            <td>{{ $lesson->sort_order }}</td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('admin.lessons.preview', $lesson) }}" class="btn btn-secondary" style="font-size:12px;" target="_blank">Preview</a>
                                <a href="{{ route('admin.lessons.edit', [$lesson, 'return_to' => 'subStrand']) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                @if ($lesson->status !== 'archived')
                                    <form action="{{ route('admin.lessons.archive', $lesson) }}" method="POST" style="display:inline;" onsubmit="return confirm('Archive this lesson?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger" style="font-size:12px;">Archive</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.lessons.unarchive', $lesson) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">Restore</button>
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
