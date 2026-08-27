@extends('admin.layouts.app')
@section('title', 'Edit Quiz')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>✏️ Edit Quiz Settings</h3>
        <div>
            <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-secondary">← Builder</a>
            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">List</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Lesson <span style="color:#dc3545;">*</span></label>
                <select name="lesson_id" class="form-control" required>
                    <option value="">Select a lesson…</option>
                    @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected($quiz->lesson_id === $lesson->id)>
                            {{ $lesson->title }} — {{ $lesson->topic->subject->name ?? '?' }} / {{ $lesson->topic->name ?? '?' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Quiz Title <span style="color:#dc3545;">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ $quiz->title }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Instructions (shown to child)</label>
                <textarea name="instructions" class="form-control" rows="2">{{ $quiz->instructions }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Pass Threshold (%)</label>
                    <input type="number" name="pass_threshold_percent" class="form-control" value="{{ $quiz->pass_threshold_percent }}" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Attempts</label>
                    <input type="number" name="max_attempts" class="form-control" value="{{ $quiz->max_attempts }}" min="1" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" @selected($quiz->status === 'draft')>Draft</option>
                        <option value="in_review" @selected($quiz->status === 'in_review')>In Review</option>
                        <option value="published" @selected($quiz->status === 'published')>Published</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:24px;margin:16px 0;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="shuffle_questions" {{ $quiz->shuffle_questions ? 'checked' : '' }}> Shuffle questions
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="shuffle_options" {{ $quiz->shuffle_options ? 'checked' : '' }}> Shuffle options
                </label>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>
@endsection