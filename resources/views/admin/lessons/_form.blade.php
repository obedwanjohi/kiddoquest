@php
    $selTopic = old('topic_id', $lesson->topic_id ?? ($selectedTopicId ?? null));
    $ct = old('content_type', $lesson->content_type ?? 'text');
    $st = old('status', $lesson->status ?? 'draft');
@endphp

<div class="form-group">
    <label for="topic_id">Sub-Strand</label>
    <select id="topic_id" name="topic_id" class="form-control" required>
        <option value="">— Select sub-strand —</option>
        @foreach ($topics as $topic)
            <option value="{{ $topic->id }}" @selected((int) $selTopic === $topic->id)>
                @if($topic->subject){{ $topic->subject->level->name ?? '' }} → {{ $topic->subject->name }} → @endif{{ $topic->name }}
            </option>
        @endforeach
    </select>
    <small style="color:#a0aec0;">Level → Subject → Sub-Strand. Every lesson belongs to exactly one sub-strand.</small>
</div>

<div class="form-group">
    <label for="title">Lesson Title</label>
    <input type="text" id="title" name="title" class="form-control" required value="{{ old('title', $lesson->title ?? '') }}">
</div>
<div class="form-group">
    <label for="slug">Slug (auto-generated if blank)</label>
    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $lesson->slug ?? '') }}">
</div>
<div class="form-group">
    <label for="summary">Short Description</label>
    <textarea id="summary" name="summary" class="form-control" maxlength="500">{{ old('summary', $lesson->summary ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="learning_objective">Learning Objective</label>
    <textarea id="learning_objective" name="learning_objective" class="form-control" maxlength="1000" placeholder="What should the child be able to do after this lesson?">{{ old('learning_objective', $lesson->learning_objective ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="content_type">Content Type</label>
    <select id="content_type" name="content_type" class="form-control">
        <option value="text" @selected($ct === 'text')>Text</option>
        <option value="video" @selected($ct === 'video')>Video</option>
        <option value="interactive" @selected($ct === 'interactive')>Interactive</option>
    </select>
</div>
<div class="form-group">
    <label for="content">Lesson Content / Teacher Notes</label>
    <textarea id="content" name="content" class="form-control" style="min-height:120px;" placeholder="Internal reference notes for teachers/content creators (not shown to children).">{{ old('content', $lesson->content ?? '') }}</textarea>
</div>

<hr style="margin: 24px 0; border: none; border-top: 2px dashed #e2e8f0;">
<h4 style="margin-bottom: 12px; color: #1e293b;">🖼️ Thumbnail</h4>

<x-admin.media-picker
    name="thumbnail_media_id"
    label="Thumbnail Image"
    type="image"
    :value="old('thumbnail_media_id', $lesson->thumbnail_media_id ?? null)"
    :media="isset($lesson) && $lesson->thumbnail_media_id ? $lesson->thumbnailMedia : null"
    help="Shown on lesson cards (16:9 recommended)."
/>

<div style="padding:12px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;margin:16px 0;font-size:13px;color:#0c4a6e;">
    💡 <strong>Teaching videos, narration, and quizzes are now managed in Missions.</strong><br>
    After creating this lesson, click the <strong>🎯 Missions</strong> button to add learning experiences.
</div>

<hr style="margin: 24px 0; border: none; border-top: 2px dashed #e2e8f0;">
<div class="form-group" style="display:flex; gap:16px;">
    <div style="flex:1;">
        <label for="duration_minutes">Duration (minutes)</label>
        <input type="number" id="duration_minutes" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $lesson->duration_minutes ?? 5) }}" min="1" max="300">
    </div>
    <div style="flex:1;">
        <label for="sort_order">Sort Order <small style="color:#a0aec0;">(blank = end)</small></label>
        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $lesson->sort_order ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label for="status">Status</label>
    <select id="status" name="status" class="form-control">
        <option value="draft" @selected($st === 'draft')>Draft</option>
        <option value="in_review" @selected($st === 'in_review')>In Review</option>
        <option value="published" @selected($st === 'published')>Published</option>
        <option value="archived" @selected($st === 'archived')>Archived</option>
    </select>
</div>
@if (request('return_to') === 'subStrand')
    <input type="hidden" name="return_to" value="subStrand">
@endif