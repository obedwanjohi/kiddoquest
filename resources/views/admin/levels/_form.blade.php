<div class="form-group">
    <label for="curriculum_id">Curriculum</label>
    @php $currentCurriculum = old('curriculum_id', $level->curriculum_id ?? null); @endphp
    <select id="curriculum_id" name="curriculum_id" class="form-control" required>
        <option value="">— Select curriculum —</option>
        @foreach ($curricula as $c)
            <option value="{{ $c->id }}" @selected((int) $currentCurriculum === $c->id)>{{ $c->icon }} {{ $c->name }}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="name">Level Name</label>
    <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $level->name ?? '') }}">
</div>
<div class="form-group">
    <label for="code">Code (e.g. G1, PP1)</label>
    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $level->code ?? '') }}" maxlength="50">
</div>
<div class="form-group">
    <label for="slug">Slug (leave blank to auto-generate)</label>
    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $level->slug ?? '') }}">
</div>
<div class="form-group">
    <label for="stage">Stage (e.g. pre_primary, lower_primary)</label>
    <input type="text" id="stage" name="stage" class="form-control" value="{{ old('stage', $level->stage ?? '') }}" maxlength="30">
</div>
<div class="form-group" style="display:flex; gap:16px;">
    <div style="flex:1;">
        <label for="min_age">Min Age</label>
        <input type="number" id="min_age" name="min_age" class="form-control" min="0" max="18" value="{{ old('min_age', $level->min_age ?? '') }}">
    </div>
    <div style="flex:1;">
        <label for="max_age">Max Age</label>
        <input type="number" id="max_age" name="max_age" class="form-control" min="0" max="18" value="{{ old('max_age', $level->max_age ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $level->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="icon">Icon (emoji)</label>
    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $level->icon ?? '⭐') }}" maxlength="10">
</div>
<div class="form-group">
    <label for="color">Color (hex)</label>
    <input type="text" id="color" name="color" class="form-control" value="{{ old('color', $level->color ?? '#4F46E5') }}" maxlength="20">
</div>
<div class="form-group">
    <label for="sort_order">Sort Order</label>
    <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $level->sort_order ?? 0) }}">
</div>
<div class="form-group">
    <label for="status">Status</label>
    @php $currentStatus = old('status', $level->status ?? 'draft'); @endphp
    <select id="status" name="status" class="form-control">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
        <option value="archived" @selected($currentStatus === 'archived')>Archived</option>
    </select>
</div>
