@php
    $selLevel = old('level_id', $subject->level_id ?? ($selectedLevelId ?? null));
    $currentStatus = old('status', $subject->status ?? 'draft');
@endphp
<div class="form-group">
    <label for="level_id">Level</label>
    <select id="level_id" name="level_id" class="form-control" required>
        <option value="">— Select level —</option>
        @foreach ($levels as $lvl)
            <option value="{{ $lvl->id }}" @selected((int) $selLevel === $lvl->id)>
                {{ $lvl->icon }} {{ $lvl->name }}@if($lvl->curriculum) — {{ $lvl->curriculum->name }}@endif
            </option>
        @endforeach
    </select>
    <small style="color:#a0aec0;">Every subject belongs to exactly one level.</small>
</div>
<div class="form-group">
    <label for="name">Subject Name</label>
    <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $subject->name ?? '') }}">
</div>
<div class="form-group">
    <label for="slug">Slug (leave blank to auto-generate)</label>
    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $subject->slug ?? '') }}">
</div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $subject->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="icon">Icon (emoji)</label>
    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $subject->icon ?? '📚') }}" maxlength="10">
</div>
<div class="form-group">
    <label for="color">Color (hex)</label>
    <input type="text" id="color" name="color" class="form-control" value="{{ old('color', $subject->color ?? '#4F46E5') }}" maxlength="20">
</div>
<div class="form-group">
    <label for="status">Status</label>
    <select id="status" name="status" class="form-control">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
        <option value="archived" @selected($currentStatus === 'archived')>Archived</option>
    </select>
</div>
@if (request('return_to') === 'level')
    <input type="hidden" name="return_to" value="level">
@endif
