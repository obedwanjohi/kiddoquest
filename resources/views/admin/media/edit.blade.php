@extends('admin.layouts.app')
@section('title', 'Edit Media')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>✏️ Edit Media</h3>
        <div>
            <a href="{{ route('admin.media.show', $media) }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.media.update', $media) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Display Name <span style="color:#dc3545;">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ $media->name }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-control">
                    <option value="">— None —</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string) $media->subject_id === (string) $subject->id)>{{ $subject->icon }} {{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tags <span style="color:#999;font-weight:normal;">(comma-separated)</span></label>
                <input type="text" name="tags" class="form-control" value="{{ is_array($media->tags) ? implode(', ', $media->tags) : '' }}">
            </div>

            <div class="form-group">
                <label class="form-label">Alt Text</label>
                <input type="text" name="alt_text" class="form-control" value="{{ $media->alt_text }}">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ $media->description }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('admin.media.show', $media) }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection