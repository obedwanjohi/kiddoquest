<div class="form-group">
    <label for="name">Curriculum Name</label>
    <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $curriculum->name ?? '') }}">
</div>
<div class="form-group">
    <label for="code">Code (e.g. CBC, IGCSE)</label>
    <input type="text" id="code" name="code" class="form-control" value="{{ old('code', $curriculum->code ?? '') }}" maxlength="50">
</div>
<div class="form-group">
    <label for="slug">Slug (leave blank to auto-generate)</label>
    <input type="text" id="slug" name="slug" class="form-control" value="{{ old('slug', $curriculum->slug ?? '') }}">
</div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $curriculum->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label for="icon">Icon (emoji)</label>
    <input type="text" id="icon" name="icon" class="form-control" value="{{ old('icon', $curriculum->icon ?? '🎓') }}" maxlength="10">
</div>
<div class="form-group">
    <label for="color">Color (hex)</label>
    <input type="text" id="color" name="color" class="form-control" value="{{ old('color', $curriculum->color ?? '#4F46E5') }}" maxlength="20">
</div>
<div class="form-group">
    <label for="sort_order">Sort Order</label>
    <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $curriculum->sort_order ?? 0) }}">
</div>
<div class="form-group">
    <label for="status">Status</label>
    @php $currentStatus = old('status', $curriculum->status ?? 'draft'); @endphp
    <select id="status" name="status" class="form-control">
        <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
        <option value="published" @selected($currentStatus === 'published')>Published</option>
        <option value="archived" @selected($currentStatus === 'archived')>Archived</option>
    </select>
</div>
