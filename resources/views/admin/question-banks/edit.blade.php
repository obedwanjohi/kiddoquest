@extends('admin.layouts.app')
@section('title', 'Edit — ' . $questionBank->name)

@section('content')
<div class="page-header">
    <a href="{{ route('admin.question-banks.show', $questionBank) }}" class="btn-back">← {{ $questionBank->name }}</a>
    <h1>✏️ Edit Question Bank</h1>
</div>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="POST" action="{{ route('admin.question-banks.update', $questionBank) }}" class="form-card">
    @csrf @method('PUT')

    <div class="form-group">
        <label for="name">Title *</label>
        <input type="text" id="name" name="name" required class="form-input" value="{{ old('name', $questionBank->name) }}">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-input">{{ old('description', $questionBank->description) }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" class="form-input">
                <option value="">Any subject</option>
                @foreach ($subjects as $s)
                <option value="{{ $s->id }}" {{ (old('subject_id', $questionBank->subject_id) == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>



    <div class="form-row">
        <div class="form-group">
            <label for="quiz_type_id">Quiz Type</label>
            <select id="quiz_type_id" name="quiz_type_id" class="form-input">
                <option value="">Any type</option>
                @foreach ($quizTypes as $t)
                <option value="{{ $t->id }}" {{ (old('quiz_type_id', $questionBank->quiz_type_id) == $t->id) ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty" class="form-input">
                <option value="easy" {{ old('difficulty', $questionBank->difficulty) == 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="medium" {{ old('difficulty', $questionBank->difficulty) == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="hard" {{ old('difficulty', $questionBank->difficulty) == 'hard' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
    </div>



    <div class="form-row">
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="draft" {{ old('status', $questionBank->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $questionBank->status) == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ old('status', $questionBank->status) == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>

        <div class="form-group">
            <!-- Empty placeholder to maintain grid -->
        </div>
    </div>

    <button type="submit" class="btn-primary">Save Changes</button>
    <a href="{{ route('admin.question-banks.show', $questionBank) }}" class="btn-sm">Cancel</a>
</form>
@endsection