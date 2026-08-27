@php
    $selSubject = old('subject_id', $topic->subject_id ?? ($selectedSubjectId ?? null));
    $currentStatus = old('status', $topic->status ?? 'draft');
@endphp
<div class="form-group">
    <label for="subject_id">Subject</label>
    <select id="subject_id" name="subject_id" class="form-control" required>
        <option value="">— Select subject —</option>
        @foreach ($subjects as $subj)
            <option value="{{ $subj->id }}" @selected((int) $selSubject === $subj->id)>
                {{ $subj->icon }} {{ $subj->name }}@if($subj->level) — {{ $subj->level->name }}@endif
            </option>
        @endforeach
    </select>
    <small style="color:#a0aec0;">Every sub-strand belongs to exactly one subject.</small>
</div>
<div class="form-group">
    <label for="name">Sub-Strand Name</label>
    <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $topic->name ?? '') }}">
</div>
<div class="form-group">
    <label for="slug">Slug (leave blank to auto-generate)</label>
    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $topic->slug ?? '') }}">
</div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $topic->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="icon">Icon (emoji)</label>
    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $topic->icon ?? '📂') }}" maxlength="10">
</div>
<div class="form-group">
    <label for="status">Status</label>
    <select id="status" name="status" class="form-control">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
        <option value="archived" @selected($currentStatus === 'archived')>Archived</option>
    </select>
</div>
@if (request('return_to') === 'subject')
    <input type="hidden" name="return_to" value="subject">
@endif
