@csrf

@if ($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;margin-bottom:16px;">
        <strong style="color:#dc2626;">⚠️ Please fix these errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px;color:#991b1b;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-row">
    <div class="form-group">
        <label for="name">World Name <span style="color:#dc3545;">*</span></label>
        <input type="text" id="name" name="name" class="form-input" value="{{ old('name', $adventureWorld->name ?? '') }}" required placeholder="e.g., Forest of Numbers">
    </div>

    <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" class="form-input" value="{{ old('slug', $adventureWorld->slug ?? '') }}" placeholder="Auto-generated if left blank">
        <small style="color: #64748b;">Unique URL identifier.</small>
    </div>
</div>

<div class="form-group" style="margin-bottom: 20px;">
    <label for="subject_id">🎓 Linked CBC Subject / Subject Pool</label>
    <select id="subject_id" name="subject_id" class="form-input">
        <option value="">-- Auto-Detect / General Pool --</option>
        @if(isset($subjects))
            @foreach($subjects as $s)
                <option value="{{ $s->id }}" {{ old('subject_id', $adventureWorld->subject_id ?? '') == $s->id ? 'selected' : '' }}>
                    {{ $s->name }} ({{ $s->level->name ?? 'All Levels' }})
                </option>
            @endforeach
        @endif
    </select>
    <small style="color: #64748b;">Links this world directly to a CBC Subject so kids see it in their Math, Phonics, or CRE Subject Pool.</small>
</div>

<div class="form-row">
    <div class="form-group">
        <label for="theme_color">Theme Color <span style="color:#dc3545;">*</span></label>
        <div style="display: flex; gap: 8px;">
            <input type="color" id="theme_color" name="theme_color" class="form-input" value="{{ old('theme_color', $adventureWorld->theme_color ?? '#22C55E') }}" required style="width: 60px; padding: 2px;">
            <input type="text" class="form-input" value="{{ old('theme_color', $adventureWorld->theme_color ?? '#22C55E') }}" onchange="document.getElementById('theme_color').value = this.value" placeholder="#HexColor" style="flex: 1;">
        </div>
    </div>

    <div class="form-group">
        <label for="icon">Icon (Emoji) <span style="color:#dc3545;">*</span></label>
        <input type="text" id="icon" name="icon" class="form-input" value="{{ old('icon', $adventureWorld->icon ?? '🌳') }}" required placeholder="e.g., 🌳">
    </div>
</div>

<div class="form-group">
    <label for="description">Description (optional)</label>
    <textarea id="description" name="description" class="form-input" rows="3" placeholder="Brief description of the world theme...">{{ old('description', $adventureWorld->description ?? '') }}</textarea>
</div>

<div class="form-row">
    <div class="form-group">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:28px;">
            <input type="hidden" name="is_locked" value="0">
            <input type="checkbox" name="is_locked" value="1" {{ old('is_locked', $adventureWorld->is_locked ?? false) ? 'checked' : '' }} style="transform:scale(1.2);">
            <span style="font-size:14px;font-weight:600;">Lock this world</span>
        </label>
        <small style="color: #64748b; display:block; margin-left:24px;">Locked worlds require the child to earn enough stars or complete previous worlds to unlock.</small>
    </div>

    <div class="form-group">
        <label for="sort_order">Sort Order</label>
        <input type="number" id="sort_order" name="sort_order" class="form-input" value="{{ old('sort_order', $adventureWorld->sort_order ?? '') }}" min="0">
    </div>
</div>

<div style="margin-top: 24px; display: flex; gap: 12px;">
    <button type="submit" class="btn btn-primary">💾 Save Adventure World</button>
    <a href="{{ route('admin.adventure-worlds.index') }}" class="btn btn-secondary">Cancel</a>
</div>
