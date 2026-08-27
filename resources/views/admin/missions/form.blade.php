@extends('admin.layouts.app')
@section('title', isset($mission) ? 'Edit Mission' : 'New Mission')
@section('content')

@php
    $isEdit = isset($mission);
    $voiceLabels = ['leo' => '🦁 Leo', 'lily' => '🦋 Lily', 'max' => '🐻 Max', 'mia' => '🐰 Mia', 'teacher' => '👩‍🏫 Teacher', 'custom' => '⚙️ Custom'];
@endphp

<style>
.voice-pill{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:16px;border:2px solid #e2e8f0;background:#fff;cursor:pointer;font-size:12px;font-weight:600;transition:all .15s;color:#64748b;}
.voice-pill.active{border-color:#8b5cf6;background:#f3e8ff;color:#6d28d9;}
.voice-pill:hover{border-color:#a5b4fc;}
.narration-section{background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:12px;margin-top:12px;}
.narration-label{font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;}
</style>

<div class="card">
    <div class="card-header">
        <h3>{{ $isEdit ? '✏️ Edit Mission' : '➕ New Mission' }} — {{ $lesson->title }}</h3>
        <a href="{{ route('admin.lessons.missions.index', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">← Back</a>
    </div>
    <div class="card-body">
        <form action="{{ $isEdit ? route('admin.lessons.missions.update', [$lesson, $mission]) : route('admin.lessons.missions.store', $lesson) }}" method="POST">
            @csrf
            @if ($isEdit) @method('PUT') @endif

            {{-- Validation Errors --}}
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

            {{-- Basic --}}
            <div class="form-group">
                <label class="form-label">Internal Title (Admin) <span style="color:#dc3545;">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ $isEdit ? $mission->title : old('title') }}" placeholder="e.g., Counting Fruits" required>
            </div>

            <div class="form-group">
                <label class="form-label">Kid Display Title (optional)</label>
                <input type="text" name="display_title" class="form-control" value="{{ $isEdit ? $mission->display_title : old('display_title') }}" placeholder="e.g., 🍎 Help Leo Count Apples">
                <small style="color:#94a3b8;">This is what the child will see. If left blank, it falls back to the Internal Title.</small>
            </div>

            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <textarea name="description" class="form-control" rows="2" placeholder="What will the child learn?">{{ $isEdit ? $mission->description : old('description') }}</textarea>
            </div>

            {{-- Media via Media Picker --}}
            <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
            <h5 style="margin-bottom:12px;color:#4f46e5;">🖼️ Media</h5>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <x-admin.media-picker
                        name="thumbnail_media_id"
                        label="🖼️ Thumbnail"
                        type="image"
                        :value="$isEdit ? $mission->thumbnail_media_id : null"
                        :media="$isEdit ? $mission->thumbnailMedia : null"
                        help="Image shown before the mission opens."
                    />
                </div>
                <div>
                    <x-admin.media-picker
                        name="video_media_id"
                        label="🎬 Teaching Video"
                        type="video"
                        :value="$isEdit ? $mission->video_media_id : null"
                        :media="$isEdit ? $mission->videoMedia : null"
                        help="The main teaching video of the mission."
                    />
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Video URL (optional — overrides Media picker)</label>
                <input type="text" name="video_url" class="form-control" value="{{ $isEdit ? $mission->video_url : old('video_url') }}" placeholder="https://...">
                <small style="color:#94a3b8;">If both a Media video and a URL are set, the URL takes priority.</small>
            </div>

            {{-- Intro Narration --}}
            <div class="narration-section">
                <div class="narration-label">🎙️ Intro Narration (spoken before the video)</div>
                <div class="form-group">
                    <label class="form-label" style="font-size:12px;">Intro Narration Text (TTS)</label>
                    <textarea name="intro_narration_text" class="form-control" rows="2" placeholder='Hello @{{child_name}}! Today we will learn...'>{{ $isEdit ? $mission->intro_narration_text : old('intro_narration_text') }}</textarea>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-size:12px;">Intro Voice</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;" id="introPills">
                        @foreach ($voiceLabels as $key => $label)
                            <span class="voice-pill {{ ($isEdit ? $mission->intro_voice_profile : old('intro_voice_profile')) === $key ? 'active' : '' }}" onclick="selectPill('introPills','intro_voice_input','{{ $key }}',this)">{{ $label }}</span>
                        @endforeach
                    </div>
                    <input type="hidden" name="intro_voice_profile" id="intro_voice_input" value="{{ $isEdit ? $mission->intro_voice_profile : old('intro_voice_profile') }}">
                </div>
            </div>

            {{-- Video Settings --}}
            <div style="margin-top:12px;">
                <input type="hidden" name="allow_replay" value="0">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="allow_replay" value="1" {{ ($isEdit ? $mission->allow_replay : true) ? 'checked' : '' }} style="transform:scale(1.2);">
                    <span style="font-size:14px;font-weight:600;">Allow Replay — child can rewatch the video before questions</span>
                </label>
            </div>

            {{-- Outro Narration --}}
            <div class="narration-section">
                <div class="narration-label">🎙️ Outro Narration (spoken after the questions)</div>
                <div class="form-group">
                    <label class="form-label" style="font-size:12px;">Outro Narration Text (TTS)</label>
                    <textarea name="outro_narration_text" class="form-control" rows="2" placeholder='Awesome @{{child_name}}! You did great!'>{{ $isEdit ? $mission->outro_narration_text : old('outro_narration_text') }}</textarea>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label" style="font-size:12px;">Outro Voice</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;" id="outroPills">
                        @foreach ($voiceLabels as $key => $label)
                            <span class="voice-pill {{ ($isEdit ? $mission->outro_voice_profile : old('outro_voice_profile')) === $key ? 'active' : '' }}" onclick="selectPill('outroPills','outro_voice_input','{{ $key }}',this)">{{ $label }}</span>
                        @endforeach
                    </div>
                    <input type="hidden" name="outro_voice_profile" id="outro_voice_input" value="{{ $isEdit ? $mission->outro_voice_profile : old('outro_voice_profile') }}">
                </div>
            </div>

            {{-- Assessment --}}
            <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
            <h5 style="margin-bottom:12px;color:#4f46e5;">📊 Assessment</h5>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Question Bank</label>
                    <select name="question_bank_id" class="form-control">
                        <option value="">— None —</option>
                        @foreach ($questionBanks as $bank)
                            <option value="{{ $bank->id }}" {{ ($isEdit ? $mission->question_bank_id : old('question_bank_id')) == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:#94a3b8;">The mission will pull questions from this bank.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Adventure World</label>
                    <select name="adventure_world_id" class="form-control">
                        <option value="">— Unassigned —</option>
                        @isset($adventureWorlds)
                            @foreach ($adventureWorlds as $world)
                                <option value="{{ $world->id }}" {{ ($isEdit ? $mission->adventure_world_id : old('adventure_world_id', $preselectedWorldId ?? null)) == $world->id ? 'selected' : '' }}>{{ $world->name }}</option>
                            @endforeach
                        @endisset
                    </select>
                    <small style="color:#94a3b8;">The world this mission appears in.</small>
                </div>
            </div>

            <div style="margin-top:12px;margin-bottom:16px;">
                <input type="hidden" name="randomize_questions" value="0">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="randomize_questions" value="1" {{ ($isEdit ? $mission->randomize_questions : true) ? 'checked' : '' }} style="transform:scale(1.2);">
                    <span style="font-size:14px;font-weight:600;">Randomize Questions</span>
                </label>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Questions per Session</label>
                    <input type="number" name="questions_per_session" class="form-control" value="{{ $isEdit ? $mission->questions_per_session : old('questions_per_session', 10) }}" min="1" max="50">
                </div>
                <div class="form-group">
                    <label class="form-label">Pass Threshold (%)</label>
                    <input type="number" name="pass_threshold_percent" class="form-control" value="{{ $isEdit ? $mission->pass_threshold_percent : old('pass_threshold_percent', 60) }}" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Stars Reward</label>
                    <input type="number" name="stars_reward" class="form-control" value="{{ $isEdit ? $mission->stars_reward : old('stars_reward', 3) }}" min="1" max="5">
                </div>
            </div>

            {{-- Metadata --}}
            <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Estimated Minutes</label>
                    <input type="number" name="estimated_minutes" class="form-control" value="{{ $isEdit ? $mission->estimated_minutes : old('estimated_minutes', 5) }}" min="1" max="120">
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $isEdit ? $mission->sort_order : old('sort_order', $lesson->missions->count()) }}" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" {{ ($isEdit ? $mission->status : old('status', 'draft')) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="in_review" {{ ($isEdit ? $mission->status : old('status')) === 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="published" {{ ($isEdit ? $mission->status : old('status')) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:8px;margin-top:16px;">
                <button type="submit" class="btn btn-primary">{{ $isEdit ? '💾 Save Changes' : '➕ Create Mission' }}</button>
                <a href="{{ route('admin.lessons.missions.index', $lesson) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function selectPill(containerId, inputId, value, el) {
    const container = document.getElementById(containerId);
    const input = document.getElementById(inputId);
    if (!container || !input) return;
    container.querySelectorAll('.voice-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    input.value = value;
}
</script>
@endsection