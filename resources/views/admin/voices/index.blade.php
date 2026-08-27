@extends('admin.layouts.app')
@section('title', 'Voices')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎙️ Narration Voices</h3>
        <a href="{{ route('admin.voices.create') }}" class="btn btn-primary">+ New Voice</a>
    </div>
    <div class="card-body">
        <p style="color:#a0aec0; margin-bottom:16px;">Voices used for dynamic lesson narration. Lessons store a reference to a voice — update the provider or voice ID here in one place.</p>
        @if ($voices->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🎙️</div>
                <h3>No voices yet</h3>
                <a href="{{ route('admin.voices.create') }}" class="btn btn-primary">Create Voice</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Provider</th><th>Voice ID</th><th>Lang</th><th>Gender</th><th>Lessons</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($voices as $voice)
                        <tr>
                            <td><strong>{{ $voice->name }}</strong>
                                @if ($voice->description)<br><small style="color:#a0aec0;">{{ Str::limit($voice->description, 50) }}</small>@endif
                            </td>
                            <td>{{ ucfirst($voice->provider) }}</td>
                            <td>{{ $voice->voice_id ? '' : '—' }}<code>{{ $voice->voice_id }}</code></td>
                            <td>{{ $voice->language }}</td>
                            <td>{{ $voice->gender ? ucfirst($voice->gender) : '—' }}</td>
                            <td><span class="badge badge-draft">{{ $voice->lessons_count }}</span></td>
                            <td><span class="badge badge-{{ $voice->status === 'active' ? 'published' : 'archived' }}">{{ ucfirst($voice->status) }}</span></td>
                            <td style="white-space:nowrap;">
                                <button type="button" class="btn btn-secondary" style="font-size:12px;" onclick="speakSample('{{ $voice->gender }}', '{{ $voice->language }}', '{{ addslashes($voice->name) }}')" title="Preview with browser voice">▶ Preview</button>
                                <a href="{{ route('admin.voices.edit', $voice) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                                <form action="{{ route('admin.voices.toggle', $voice) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="font-size:12px;">{{ $voice->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
<script>
function speakSample(gender, lang, name) {
    if (!('speechSynthesis' in window)) { alert('Your browser does not support speech synthesis.'); return; }
    const u = new SpeechSynthesisUtterance('Hi! I am ' + name + '. This is how I will read the lesson narration.');
    const voices = window.speechSynthesis.getVoices();
    const langRe = new RegExp('^' + (lang || 'en'), 'i');
    let pick = voices.find(v => langRe.test(v.lang) && gender === 'female' && /female|samantha|zira|google uk english female/i.test(v.name))
            || voices.find(v => langRe.test(v.lang) && gender === 'male' && /male|david|daniel/i.test(v.name))
            || voices.find(v => langRe.test(v.lang));
    if (pick) u.voice = pick;
    u.rate = 0.95; u.pitch = gender === 'male' ? 0.9 : 1.1;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(u);
}
if ('speechSynthesis' in window) { window.speechSynthesis.getVoices(); }
</script>
@endsection
