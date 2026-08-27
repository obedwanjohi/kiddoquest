@php
    $gender = old('gender', $voice->gender ?? '');
    $provider = old('provider', $voice->provider ?? 'browser');
    $status = old('status', $voice->status ?? 'active');
@endphp
<div class="form-group">
    <label for="name">Voice Name</label>
    <input type="text" id="name" name="name" class="form-control" required value="{{ old('name', $voice->name ?? '') }}" placeholder="e.g. Leo, Teacher Mary">
</div>
<div class="form-group">
    <label for="provider">Provider</label>
    <select id="provider" name="provider" class="form-control" required>
        @foreach (['browser' => 'Browser (built-in TTS)', 'elevenlabs' => 'ElevenLabs', 'polly' => 'Amazon Polly', 'openai' => 'OpenAI'] as $val => $label)
            <option value="{{ $val }}" @selected($provider === $val)>{{ $label }}</option>
        @endforeach
    </select>
    <small style="color:#a0aec0;">The lesson stores only a reference — change the provider/voice here in one place.</small>
</div>
<div class="form-group">
    <label for="voice_id">Provider Voice ID</label>
    <input type="text" id="voice_id" name="voice_id" class="form-control" value="{{ old('voice_id', $voice->voice_id ?? '') }}" placeholder="e.g. 21m00Tcm4TlvDq8ikWAM (optional for browser)">
</div>
<div class="form-group" style="display:flex; gap:16px;">
    <div style="flex:1;">
        <label for="language">Language</label>
        <input type="text" id="language" name="language" class="form-control" required value="{{ old('language', $voice->language ?? 'en') }}" maxlength="10">
    </div>
    <div style="flex:1;">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" class="form-control">
            <option value="">—</option>
            <option value="female" @selected($gender === 'female')>Female</option>
            <option value="male" @selected($gender === 'male')>Male</option>
            <option value="neutral" @selected($gender === 'neutral')>Neutral</option>
        </select>
    </div>
</div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" class="form-control">{{ old('description', $voice->description ?? '') }}</textarea>
</div>
<div class="form-group" style="display:flex; gap:16px;">
    <div style="flex:1;">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
    </div>
    <div style="flex:1;">
        <label for="sort_order">Sort Order</label>
        <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', $voice->sort_order ?? 0) }}">
    </div>
</div>
