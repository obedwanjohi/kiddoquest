@extends('admin.layouts.app')
@section('title', 'New Question Bank')

@section('content')
<div class="page-header">
    <a href="{{ route('admin.question-banks.index') }}" class="btn-back">← Question Banks</a>
    <h1>🏦 New Question Bank</h1>
</div>

<form method="POST" action="{{ route('admin.question-banks.store') }}" class="form-card">
    @csrf

    <div class="form-group">
        <label for="name">Title *</label>
        <input type="text" id="name" name="name" required class="form-input" value="{{ old('name') }}" placeholder="e.g., Main Assessment, Practice Questions, Revision…">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" class="form-input" placeholder="What skills does this bank test?">{{ old('description') }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="subject_id">Subject</label>
            <select id="subject_id" name="subject_id" class="form-input">
                <option value="">Any subject</option>
                @foreach ($subjects as $s)
                <option value="{{ $s->id }}" {{ old('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>



    <div class="form-row">
        <div class="form-group">
            <label for="quiz_type_id">Quiz Type</label>
            <select id="quiz_type_id" name="quiz_type_id" class="form-input">
                <option value="">Any type</option>
                @foreach ($quizTypes as $t)
                <option value="{{ $t->id }}" {{ old('quiz_type_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="difficulty">Difficulty</label>
            <select id="difficulty" name="difficulty" class="form-input">
                <option value="easy" {{ old('difficulty', 'medium') == 'easy' ? 'selected' : '' }}>Easy</option>
                <option value="medium" {{ old('difficulty', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="hard" {{ old('difficulty', 'medium') == 'hard' ? 'selected' : '' }}>Hard</option>
            </select>
        </div>
    </div>



    <div class="form-row">
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', 'draft') == 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ old('status', 'draft') == 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>

        <div class="form-group">
            <!-- Empty placeholder to maintain grid, or can be removed if not needed -->
        </div>
    </div>

    <button type="submit" class="btn-primary">Create Bank</button>
</form>
@endsection