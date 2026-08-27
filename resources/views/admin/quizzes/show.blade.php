@extends('admin.layouts.app')
@section('title', 'Quiz Builder')
@section('content')

@php
    $voiceLabels = ['leo' => '🦁 Leo', 'lily' => '🦋 Lily', 'max' => '🐻 Max', 'mia' => '🐰 Mia', 'teacher' => '👩‍🏫 Teacher', 'custom' => '⚙️ Custom'];

    // Media Requirements Panel per type
    $mediaRequirements = [
        'QT-01' => ['📘 Multiple Choice', '📝 Prompt text (required — this is spoken via TTS)', '🖼️ 1+ question images (optional)', '🎯 2–6 answer options (text/image/audio)', '🎙️ Voice Profile (TTS fallback)'],
        'QT-02' => ['✅ True / False', '📝 Prompt text (required — this is spoken via TTS)', '🎙️ Voice Profile (TTS fallback)', '🖼️ Optional image', '🎯 True/False auto-seeded'],
        'QT-03' => ['🔗 Matching', '📝 Prompt text', '🔗 2–6 left/right pairs', '🖼️ Each side can be text or image', '🎙️ Voice Profile (TTS fallback)'],
        'QT-04' => ['📦 Drag Sort', '📝 Prompt text', '📦 2+ category buckets', '🎯 2+ sort items (text/image)', '🎙️ Voice Profile (TTS fallback)'],
        'QT-05' => ['🔢 Sequence', '📝 Prompt text', '🎯 3+ items in correct order', '🎙️ Voice Profile (TTS fallback)'],
        'QT-06' => ['🔊 Listen & Choose', '🔊 1 audio prompt', '🎯 2+ answer options', '🎙️ Voice Profile (TTS fallback)'],
        'QT-07' => ['🎤 Speak & Repeat', '🔊 1 audio of word/phrase', '🎙️ Voice Profile (TTS fallback)', '📝 Expected pronunciation text'],
        'QT-08' => ['✏️ Spell / Fill', '📝 Prompt text', '🎯 Letter/word tiles', '🎙️ Voice Profile (TTS fallback)'],
        'QT-09' => ['🔢 Count Objects', '🖼️ 1 counting image', '🎯 Number options', '🎙️ Voice Profile (TTS fallback)'],
        'QT-10' => ['🔁 Complete Pattern', '📝 Pattern in prompt', '🎯 Possible next items', '🎙️ Voice Profile (TTS fallback)'],
        'QT-11' => ['🃏 Memory Match', '🃏 2–6 card pairs', '🖼️ Text or image per card', '🎙️ Voice Profile (TTS fallback)'],
        'QT-12' => ['✍️ Tracing', '🖼️ 1 dashed/outline PNG', '🎙️ Voice Profile (TTS fallback)'],
        'QT-13' => ['🔍 Spot & Find', '🖼️ 1 scene image (16:9)', '🎯 1+ tap targets', '🎙️ Voice Profile (TTS fallback)'],
    ];
@endphp

<style>
.qb-enhanced-section{background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:12px;margin-top:12px;}
.qb-enhanced-label{font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;display:flex;align-items:center;gap:4px;}
.qb-voice-pill{display:inline-flex;align-items:center;gap:4px;padding:5px 12px;border-radius:16px;border:2px solid #e2e8f0;background:#fff;cursor:pointer;font-size:12px;font-weight:600;transition:all .15s;color:#64748b;}
.qb-voice-pill.active{border-color:#8b5cf6;background:#f3e8ff;color:#6d28d9;box-shadow:0 0 0 2px rgba(139,92,246,0.2);}
.qb-voice-pill:hover{border-color:#a5b4fc;}
.qb-meta-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:2px 8px;border-radius:10px;margin-left:6px;}
.qb-meta-difficulty-easy{background:#dcfce7;color:#166534;}
.qb-meta-difficulty-medium{background:#fef9c3;color:#854d0e;}
.qb-meta-difficulty-hard{background:#fee2e2;color:#991b1b;}
.qb-meta-cbc{background:#dbeafe;color:#1e40af;}
.qb-meta-voice{background:#f3e8ff;color:#6d28d9;}
.qb-narration-preview{margin-top:8px;background:linear-gradient(135deg,#faf5ff,#eff6ff);border:1px solid #e9d5ff;border-radius:8px;padding:10px;font-size:13px;color:#4c1d95;}
.qb-narration-preview .voice-tag{background:#8b5cf6;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;display:inline-block;margin-bottom:4px;}
.qb-narration-preview .prompt-preview{font-weight:600;margin-top:4px;color:#1e1b4b;}
.qb-custom-voice-input{margin-top:6px;display:none;}
.qb-custom-voice-input input{font-size:12px;padding:4px 8px;border:1px solid #c7d2fe;border-radius:6px;width:200px;}
.qb-requirements{margin-top:8px;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:8px 10px;font-size:11px;color:#92400e;}
.qb-requirements strong{color:#78350f;}
.qb-requirements ul{margin:4px 0 0;padding-left:16px;}
.qb-multi-img-grid{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;}
.qb-multi-img-item{position:relative;width:72px;height:72px;border-radius:6px;overflow:hidden;border:2px solid #e2e8f0;}
.qb-multi-img-item img{width:100%;height:100%;object-fit:cover;}
.qb-multi-img-remove{position:absolute;top:0;right:0;background:rgba(239,68,68,0.9);color:#fff;border:none;border-radius:0 0 0 6px;padding:2px 5px;font-size:10px;cursor:pointer;}
.qb-add-img-btn{width:72px;height:72px;border:2px dashed #c7d2fe;border-radius:6px;background:#faf5ff;color:#8b5cf6;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:22px;}
.qb-add-img-btn:hover{background:#f3e8ff;border-color:#8b5cf6;}
</style>

{{-- Quiz Header --}}
<div class="card">
    <div class="card-header">
        <h3>🎯 {{ $quiz->title }}</h3>
        <div>
            <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-secondary" style="font-size:12px;">⚙ Settings</a>
            <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary" style="font-size:12px;">← List</a>
        </div>
    </div>
    <div class="card-body" style="padding:12px 20px;">
        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:14px;color:#666;">
            <span><strong>📖 Lesson:</strong> {{ $quiz->lesson->title ?? '—' }}</span>
            <span><strong>📚 Subject:</strong> {{ $quiz->lesson->topic->subject->name ?? '—' }}</span>
            <span><strong>📊 Pass:</strong> {{ $quiz->pass_threshold_percent }}%</span>
            <span><strong>🔁 Attempts:</strong> {{ $quiz->max_attempts }}</span>
            <span><strong>📌 Status:</strong> <span class="badge badge-{{ $quiz->status }}">{{ ucfirst(str_replace('_', ' ', $quiz->status)) }}</span></span>
        </div>
    </div>
</div>

{{-- Add Question Form --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <h4>➕ Add Question</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.quizzes.questions.store', $quiz) }}" method="POST" id="addQuestionForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Question Type <span style="color:#dc3545;">*</span> <span style="font-size:12px;color:#999;">— click a card to select</span></label>
                <input type="hidden" name="quiz_type_id" id="quiz_type_input" required>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;margin-top:8px;">
                    @foreach ($quizTypes as $qt)
                        <label onclick="selectType({{ $qt->id }}, '{{ $qt->code }}', this)" style="cursor:pointer;border:2px solid #e2e8f0;border-radius:10px;padding:10px;text-align:center;transition:all 0.2s;display:block;" class="type-card" data-id="{{ $qt->id }}" data-code="{{ $qt->code }}">
                            <div style="font-size:28px;">{{ $qt->icon }}</div>
                            <div style="font-size:12px;font-weight:700;color:#1e293b;margin-top:4px;">{{ $qt->name }}</div>
                            <div style="font-size:10px;color:#999;font-family:monospace;margin-top:2px;">{{ $qt->code }}</div>
                            @if ($qt->has_options)
                                <div style="font-size:10px;color:#22c55e;margin-top:2px;">✓ has options</div>
                            @else
                                <div style="font-size:10px;color:#f59e0b;margin-top:2px;">⚡ {{ $qt->interaction_mode }}</div>
                            @endif
                        </label>
                    @endforeach
                </div>
                <div id="requirementsPanel" class="qb-requirements" style="display:none;">
                    <strong>📋 Recommended assets for this type:</strong>
                    <ul id="requirementsList"></ul>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Question Prompt <span style="color:#dc3545;">*</span></label>
                <input type="text" name="prompt" id="addPrompt" class="form-control" placeholder="e.g., How many apples do you see?" required oninput="updateVoicePreview('add')">
                <small style="color:#94a3b8;">This text is shown on screen AND spoken via TTS using the selected Voice Profile (if no audio is uploaded).</small>
            </div>

            <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
            <h5 style="margin-bottom:12px;color:#4f46e5;">🖼️ Question Media</h5>

            {{-- Primary Image --}}
            <div style="margin-bottom:12px;">
                <label class="form-label" style="font-size:12px;">🖼️ Primary Image</label>
                <div style="display:flex;gap:8px;align-items:center;">
                    <input type="text" name="prompt_image_url" id="addPrimaryImg" class="form-control" placeholder="Primary image URL (auto-filled)" readonly style="flex:1;font-size:13px;">
                    <button type="button" class="qb-media-btn" onclick="openAddImagePicker('addPrimaryImg')">🖼️ Browse</button>
                </div>
            </div>

            {{-- Additional Images --}}
            <div>
                <label class="form-label" style="font-size:12px;">📸 Additional Images</label>
                <div id="addImagesGrid" class="qb-multi-img-grid"></div>
                <input type="hidden" name="additional_images" id="additionalImagesInput" value="">
                <button type="button" class="qb-add-img-btn" style="margin-top:6px;width:auto;padding:6px 14px;font-size:13px;" onclick="addAdditionalImage()">➕ Add Image</button>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:16px;">
                <x-admin.media-picker
                    name="prompt_audio_url"
                    label="Prompt Audio 🔊 (optional — overrides TTS)"
                    type="audio"
                    output="url"
                    help="If uploaded, this audio plays instead of TTS. If empty, the Prompt is read using the Voice Profile."
                />
            </div>

            <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Points</label>
                    <input type="number" name="points" class="form-control" value="1" min="1" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ $quiz->questions->count() }}" min="0">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Hint (optional)</label>
                    <input type="text" name="hint" class="form-control" placeholder="Shown if child is stuck">
                </div>
                <div class="form-group">
                    <label class="form-label">Explanation (optional)</label>
                    <input type="text" name="explanation" class="form-control" placeholder="Shown after answering">
                </div>
                <div class="form-group">
                    <label class="form-label">Difficulty</label>
                    <select name="difficulty" class="form-control">
                        <option value="">— Select —</option>
                        <option value="easy">🟢 Easy</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="hard">🔴 Hard</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">CBC Outcome Code</label>
                <input type="text" name="cbc_outcome_code" class="form-control" placeholder="e.g., ECDE-1.2.3">
            </div>

            {{-- Voice Profile (TTS fallback) --}}
            <div class="qb-enhanced-section">
                <div class="qb-enhanced-label">🎙️ Voice Profile (used for TTS when no Prompt Audio is uploaded)</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;" id="voicePills-add">
                    @foreach ($voiceLabels as $key => $label)
                        <span class="qb-voice-pill" onclick="selectVoice('add', '{{ $key }}', this)">{{ $label }}</span>
                    @endforeach
                </div>
                <input type="hidden" name="voice_profile" id="voiceInput-add" value="">
                <div class="qb-custom-voice-input" id="customVoice-add">
                    <input type="text" placeholder="Enter custom voice name…" oninput="document.getElementById('voiceInput-add').value = this.value;">
                </div>
                {{-- Live Voice Preview --}}
                <div id="voicePreview-add" class="qb-narration-preview" style="display:none;">
                    <span class="voice-tag" id="voicePreviewTag-add">—</span>
                    <div class="prompt-preview" id="voicePreviewPrompt-add"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top:12px;">+ Add Question</button>
        </form>
    </div>
</div>

{{-- Questions List --}}
@if ($quiz->questions->isNotEmpty())
    @foreach ($quiz->questions as $index => $question)
        <div class="card" style="margin-top:16px;border-left:4px solid #4f46e5;">
            <div class="card-header">
                <h4>
                    <span style="color:#999;">Q{{ $index + 1 }}.</span>
                    {{ $question->prompt }}
                    <span style="font-size:12px;background:#e0e7ff;color:#4f46e5;padding:2px 10px;border-radius:12px;margin-left:8px;">
                        {{ $question->quizType->icon }} {{ $question->quizType->code }}
                    </span>
                    <span style="font-size:12px;color:#999;margin-left:8px;">({{ $question->points }} pt{{ $question->points > 1 ? 's' : '' }})</span>
                    @if ($question->difficulty)
                        <span class="qb-meta-badge qb-meta-difficulty-{{ $question->difficulty }}">
                            @if ($question->difficulty === 'easy') 🟢 @elseif ($question->difficulty === 'medium') 🟡 @else 🔴 @endif
                            {{ ucfirst($question->difficulty) }}
                        </span>
                    @endif
                    @if ($question->cbc_outcome_code)
                        <span class="qb-meta-badge qb-meta-cbc">📋 {{ $question->cbc_outcome_code }}</span>
                    @endif
                    @if ($question->voice_profile)
                        <span class="qb-meta-badge qb-meta-voice">🎙️ {{ $voiceLabels[$question->voice_profile] ?? ucfirst($question->voice_profile) }}</span>
                    @endif
                </h4>
                <div style="display:flex;gap:8px;">
                    <button onclick="toggleEdit({{ $question->id }})" class="btn btn-secondary" style="font-size:12px;">✏ Edit</button>
                    <form action="{{ route('admin.quizzes.questions.duplicate', [$quiz, $question]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Duplicate this question with all its options, images, voice, and metadata?')">
                        @csrf
                        <button type="submit" class="btn btn-secondary" style="font-size:12px;">📋 Duplicate</button>
                    </form>
                    <form action="{{ route('admin.quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this question and all its options?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size:12px;">🗑 Delete</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @php
                    $additionalImages = $question->metadata['additional_images'] ?? [];
                    $hasMedia = $question->prompt_image_url || $question->prompt_audio_url || $question->hint || $question->explanation || !empty($additionalImages);
                @endphp

                @if ($hasMedia)
                    <div style="background:#f8fafc;padding:12px 14px;border-radius:8px;margin-bottom:12px;">
                        @if ($question->prompt_image_url || !empty($additionalImages))
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                                @if ($question->prompt_image_url)
                                    <div>
                                        <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px;">🖼️ Primary Image</div>
                                        <img src="{{ $question->prompt_image_url }}" alt="prompt" style="max-width:150px;max-height:100px;border-radius:8px;border:1px solid #e2e8f0;" onerror="this.style.display='none'">
                                    </div>
                                @endif
                                @if (!empty($additionalImages))
                                    <div>
                                        <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px;">📸 Additional Images ({{ count($additionalImages) }})</div>
                                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                            @foreach ($additionalImages as $img)
                                                <img src="{{ $img }}" alt="additional" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;" onerror="this.style.display='none'">
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if ($question->prompt_audio_url)
                            <div style="margin-bottom:8px;">
                                <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:4px;">🔊 Prompt Audio</div>
                                <audio controls preload="none" style="height:32px;width:100%;max-width:240px;">
                                    <source src="{{ $question->prompt_audio_url }}">
                                </audio>
                            </div>
                        @endif
                        @if ($question->voice_profile)
                            <div class="qb-narration-preview" style="margin-top:8px;">
                                <span class="voice-tag">Voice: {{ $voiceLabels[$question->voice_profile] ?? ucfirst($question->voice_profile) }}</span>
                                <div class="prompt-preview">Prompt: {{ $question->prompt }}</div>
                            </div>
                        @endif
                        @if ($question->hint) <div style="margin-top:8px;font-size:13px;">💡 <strong>Hint:</strong> {{ $question->hint }}</div> @endif
                        @if ($question->explanation) <div style="font-size:13px;">📚 <strong>Explanation:</strong> {{ $question->explanation }}</div> @endif
                    </div>
                @endif

                {{-- Inline Edit Form --}}
                <div id="edit-form-{{ $question->id }}" style="display:none;background:#eff6ff;padding:16px;border-radius:8px;margin-bottom:12px;border:2px solid #bfdbfe;">
                    <form action="{{ route('admin.quizzes.questions.update', [$quiz, $question]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label class="form-label">Prompt</label>
                            <input type="text" name="prompt" id="editPrompt-{{ $question->id }}" class="form-control" value="{{ $question->prompt }}" required oninput="updateVoicePreview({{ $question->id }})">
                        </div>

                        <div class="form-group" style="margin-top:-10px; margin-bottom:12px;">
                            <label class="form-label" style="font-size:11px; color:#64748b;">🗣️ TTS Override (Hidden reading text)</label>
                            <input type="text" name="narration_text" class="form-control" value="{{ $question->narration_text }}" placeholder="If the prompt is 'C _ T', type 'Cat' here so the engine reads it properly!" style="font-size:12px; border-color:#e2e8f0; background:#f8fafc;">
                        </div>

                        <div style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:12px;">🖼️ Primary Image</label>
                            <div style="display:flex;gap:8px;align-items:center;">
                                <input type="text" name="prompt_image_url" id="editPrimaryImg-{{ $question->id }}" class="form-control" value="{{ $question->prompt_image_url }}" placeholder="Image URL" style="flex:1;font-size:13px;">
                                <button type="button" class="qb-media-btn" onclick="openAddImagePicker('editPrimaryImg-{{ $question->id }}')">🖼️ Browse</button>
                            </div>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label class="form-label" style="font-size:12px;">📸 Additional Images</label>
                            <div id="editImagesGrid-{{ $question->id }}" class="qb-multi-img-grid">
                                @if (!empty($additionalImages))
                                    @foreach ($additionalImages as $i => $img)
                                        <div class="qb-multi-img-item" id="editImg-{{ $question->id }}-{{ $i }}">
                                            <img src="{{ $img }}" onerror="this.style.display='none'">
                                            <button type="button" class="qb-multi-img-remove" onclick="removeEditImage({{ $question->id }}, {{ $i }})">✕</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <input type="hidden" name="additional_images" id="editAdditionalImages-{{ $question->id }}" value="{{ !empty($additionalImages) ? json_encode($additionalImages) : '' }}">
                            <button type="button" class="qb-add-img-btn" style="margin-top:6px;width:auto;padding:6px 14px;font-size:13px;" onclick="addEditImage({{ $question->id }})">➕ Add Image</button>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <x-admin.media-picker
                                name="prompt_audio_url"
                                label="Prompt Audio (overrides TTS)"
                                type="audio"
                                output="url"
                                :value="$question->prompt_audio_url"
                            />
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:12px;">
                            <div class="form-group">
                                <label class="form-label">Hint</label>
                                <input type="text" name="hint" class="form-control" value="{{ $question->hint }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Explanation</label>
                                <input type="text" name="explanation" class="form-control" value="{{ $question->explanation }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Difficulty</label>
                                <select name="difficulty" class="form-control">
                                    <option value="">— Select —</option>
                                    <option value="easy" @if($question->difficulty === 'easy') selected @endif>🟢 Easy</option>
                                    <option value="medium" @if($question->difficulty === 'medium') selected @endif>🟡 Medium</option>
                                    <option value="hard" @if($question->difficulty === 'hard') selected @endif>🔴 Hard</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">CBC Outcome Code</label>
                            <input type="text" name="cbc_outcome_code" class="form-control" value="{{ $question->cbc_outcome_code }}" placeholder="e.g., ECDE-1.2.3">
                        </div>

                        {{-- Voice Profile (TTS fallback) --}}
                        <div class="qb-enhanced-section">
                            <div class="qb-enhanced-label">🎙️ Voice Profile (TTS fallback when no Prompt Audio)</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;" id="voicePills-{{ $question->id }}">
                                @foreach ($voiceLabels as $key => $label)
                                    <span class="qb-voice-pill {{ $question->voice_profile === $key ? 'active' : '' }}" onclick="selectVoice({{ $question->id }}, '{{ $key }}', this)">{{ $label }}</span>
                                @endforeach
                            </div>
                            <input type="hidden" name="voice_profile" id="voiceInput-{{ $question->id }}" value="{{ $question->voice_profile }}">
                            <div class="qb-custom-voice-input" id="customVoice-{{ $question->id }}" style="@if($question->voice_profile !== 'custom') display:none; @endif">
                                <input type="text" value="{{ $question->voice_profile !== 'custom' && $question->voice_profile ? $question->voice_profile : '' }}" placeholder="Enter custom voice name…" oninput="document.getElementById('voiceInput-{{ $question->id }}').value = this.value;">
                            </div>
                            <div id="voicePreview-{{ $question->id }}" class="qb-narration-preview" style="@if(!$question->voice_profile) display:none; @endif">
                                <span class="voice-tag" id="voicePreviewTag-{{ $question->id }}">{{ $voiceLabels[$question->voice_profile] ?? ucfirst($question->voice_profile ?? '') }}</span>
                                <div class="prompt-preview" id="voicePreviewPrompt-{{ $question->id }}">Prompt: {{ $question->prompt }}</div>
                            </div>
                        </div>

                        <div style="display:flex;gap:8px;margin-top:12px;">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <button type="button" onclick="toggleEdit({{ $question->id }})" class="btn btn-secondary">Cancel</button>
                        </div>
                    </form>
                </div>

                {{-- Options List --}}
                @if ($question->quizType->has_options)
                    <h5 style="margin-bottom:8px;">Answer Options:</h5>
                    @if ($question->options->isNotEmpty())
                        <table class="table" style="margin-bottom:12px;">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Content</th>
                                    <th style="width:80px;">Type</th>
                                    <th style="width:80px;">Correct?</th>
                                    <th style="width:60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($question->options as $optIndex => $option)
                                    <tr>
                                        <td>{{ $optIndex + 1 }}</td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                                @if ($option->image_url)
                                                    <img src="{{ $option->image_url }}" alt="option" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;" onerror="this.style.display='none'">
                                                @endif
                                                @if ($option->text_value)
                                                    <span style="font-size:14px;font-weight:600;">{{ $option->text_value }}</span>
                                                @endif
                                                @if ($option->audio_url)
                                                    <audio controls preload="none" style="height:28px;width:140px;">
                                                        <source src="{{ $option->audio_url }}">
                                                    </audio>
                                                @endif
                                                @if (!$option->text_value && !$option->image_url && !$option->audio_url)
                                                    <span style="color:#cbd5e1;">— empty —</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $types = [];
                                                if ($option->text_value) $types[] = '📝';
                                                if ($option->image_url) $types[] = '🖼️';
                                                if ($option->audio_url) $types[] = '🔊';
                                                $typeLabel = count($types) > 1 ? 'mixed' : $option->content_type;
                                            @endphp
                                            <span class="badge badge-{{ $option->content_type === 'text' ? 'draft' : ($option->content_type === 'image' ? 'published' : ($option->content_type === 'mixed' ? 'in_review' : 'in_review')) }}">
                                                {{ implode(' ', $types) }} {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td>@if ($option->is_correct) ✅ <strong>Yes</strong> @else ❌ @endif</td>
                                        <td>
                                            <form action="{{ route('admin.quizzes.options.destroy', [$quiz, $question, $option]) }}" method="POST" onsubmit="return confirm('Delete this option?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" style="font-size:11px;padding:2px 8px;">✕</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p style="color:#999;font-style:italic;margin-bottom:12px;">No options yet. Add answer choices below.</p>
                    @endif

                    <form action="{{ route('admin.quizzes.options.store', [$quiz, $question]) }}" method="POST" style="background:#f8fafc;padding:16px;border-radius:8px;">
                        @csrf
                        <div style="background:#eef2ff;border:1px solid #c7d2fe;border-radius:6px;padding:8px 10px;margin-bottom:12px;font-size:11px;color:#3730a3;">
                            💡 <strong>Flexible Options:</strong> Fill in any combination of Text, Image, and Audio. Leave fields blank to skip them.
                        </div>
                        <input type="hidden" name="content_type" value="mixed">

                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label" style="font-size:12px;">📝 Text (optional)</label>
                            <input type="text" name="text_value" class="form-control" style="font-size:13px;" placeholder="e.g., Apple, True, 5…">
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                            <div>
                                <label class="form-label" style="font-size:12px;">🖼️ Image (optional)</label>
                                <x-admin.media-picker name="image_url" type="image" output="url" compact="true" />
                            </div>
                            <div>
                                <label class="form-label" style="font-size:12px;">🔊 Audio (optional)</label>
                                <x-admin.media-picker name="audio_url" type="audio" output="url" compact="true" />
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:end;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:12px;">Match Key</label>
                                <input type="text" name="match_key" class="form-control" style="font-size:13px;" placeholder="auto or position #">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label" style="font-size:12px;">Correct?</label>
                                <select name="is_correct" class="form-control" style="font-size:13px;">
                                    <option value="0">No</option>
                                    <option value="1">✅ Yes</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" style="font-size:13px;">+ Add Option</button>
                        </div>
                    </form>
                @else
                    <p style="color:#999;font-style:italic;">This question type ({{ $question->quizType->icon }} {{ $question->quizType->name }}) does not use options — it's a {{ $question->quizType->interaction_mode }} interaction.</p>
                @endif
            </div>
        </div>
    @endforeach
@else
    <div class="card" style="margin-top:16px;">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon">❓</div>
                <h3>No questions yet</h3>
                <p>Use the form above to add your first question.</p>
            </div>
        </div>
    </div>
@endif

<script>
const MEDIA_REQUIREMENTS = @json($mediaRequirements);
let addImageUrls = [];

function selectType(id, code, card) {
    document.getElementById('quiz_type_input').value = id;
    document.querySelectorAll('.type-card').forEach(c => { c.style.borderColor = '#e2e8f0'; c.style.background = 'white'; });
    card.style.borderColor = '#4f46e5';
    card.style.background = '#eef2ff';
    const panel = document.getElementById('requirementsPanel');
    const list = document.getElementById('requirementsList');
    if (MEDIA_REQUIREMENTS[code]) {
        list.innerHTML = MEDIA_REQUIREMENTS[code].map(item => `<li>${item}</li>`).join('');
        panel.style.display = 'block';
    } else { panel.style.display = 'none'; }
}

function selectVoice(scope, voice, el) {
    const pillsContainer = document.getElementById('voicePills-' + scope);
    const hiddenInput = document.getElementById('voiceInput-' + scope);
    const customDiv = document.getElementById('customVoice-' + scope);
    if (!pillsContainer || !hiddenInput) return;
    pillsContainer.querySelectorAll('.qb-voice-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    if (voice === 'custom') {
        if (customDiv) customDiv.style.display = 'block';
        const customInput = customDiv ? customDiv.querySelector('input') : null;
        hiddenInput.value = customInput ? customInput.value : '';
    } else {
        if (customDiv) customDiv.style.display = 'none';
        hiddenInput.value = voice;
    }
    updateVoicePreview(scope);
}

function updateVoicePreview(scope) {
    const promptEl = document.getElementById('addPrompt') || document.getElementById('editPrompt-' + scope);
    const narrationEl = document.querySelector(`input[name="narration_text"][oninput*="${scope}"]`) || document.querySelector(`#editPrompt-${scope}`).closest('.qb-question-card').querySelector('input[name="narration_text"]');
    const voiceInput = document.getElementById('voiceInput-' + scope);
    const preview = document.getElementById('voicePreview-' + scope);
    const voiceTag = document.getElementById('voicePreviewTag-' + scope);
    const promptPreview = document.getElementById('voicePreviewPrompt-' + scope);
    if (!preview) return;
    const prompt = promptEl ? promptEl.value.trim() : '';
    const narration = narrationEl ? narrationEl.value.trim() : '';
    const voice = voiceInput ? voiceInput.value : '';
    const labels = {'leo':'🦁 Leo','lily':'🦋 Lily','max':'🐻 Max','mia':'🐰 Mia','teacher':'👩‍🏫 Teacher','custom':'⚙️ Custom'};
    if (voice) {
        preview.style.display = 'block';
        if (voiceTag) voiceTag.textContent = 'Voice: ' + (labels[voice] || voice);
        if (promptPreview) promptPreview.textContent = 'Plays: ' + (narration || prompt || '(empty)');
    } else {
        preview.style.display = 'block';
        if (voiceTag) voiceTag.textContent = 'Voice: Default Browser Voice';
        if (promptPreview) promptPreview.textContent = 'Plays: ' + (narration || prompt || '(empty)');
    }
}

function addAdditionalImage() {
    openAddImagePicker(null, function(url) { addImageUrls.push(url); renderAddImages(); });
}
function renderAddImages() {
    const grid = document.getElementById('addImagesGrid');
    const input = document.getElementById('additionalImagesInput');
    if (!grid || !input) return;
    input.value = addImageUrls.length ? JSON.stringify(addImageUrls) : '';
    grid.innerHTML = addImageUrls.map((url, i) => `<div class="qb-multi-img-item"><img src="${url}" onerror="this.style.display='none'"><button type="button" class="qb-multi-img-remove" onclick="removeAddImage(${i})">✕</button></div>`).join('');
}
function removeAddImage(index) { addImageUrls.splice(index, 1); renderAddImages(); }

function addEditImage(qid) {
    openAddImagePicker(null, function(url) {
        const input = document.getElementById('editAdditionalImages-' + qid);
        const grid = document.getElementById('editImagesGrid-' + qid);
        if (!input || !grid) return;
        let urls = []; try { urls = JSON.parse(input.value || '[]'); } catch(e) {}
        urls.push(url); input.value = JSON.stringify(urls);
        const idx = urls.length - 1;
        const div = document.createElement('div');
        div.className = 'qb-multi-img-item'; div.id = `editImg-${qid}-${idx}`;
        div.innerHTML = `<img src="${url}" onerror="this.style.display='none'"><button type="button" class="qb-multi-img-remove" onclick="removeEditImage(${qid}, ${idx})">✕</button>`;
        grid.appendChild(div);
    });
}
function removeEditImage(qid, idx) {
    const input = document.getElementById('editAdditionalImages-' + qid);
    const el = document.getElementById(`editImg-${qid}-${idx}`);
    if (!input) return;
    let urls = []; try { urls = JSON.parse(input.value || '[]'); } catch(e) {}
    urls[idx] = null; urls = urls.filter(u => u);
    input.value = urls.length ? JSON.stringify(urls) : '';
    if (el) el.remove();
}

function openAddImagePicker(targetInputId, callback) {
    const url = '{{ route("admin.media.search") }}?type=image';
    fetch(url).then(r => r.json()).then(data => {
        if (!data.items || data.items.length === 0) { alert('No images found. Upload images in the Media section first.'); return; }
        showImagePickerModal(data.items, targetInputId, callback);
    }).catch(() => alert('Could not load media.'));
}

let ipModalCallback = null; let ipModalTarget = null;
function showImagePickerModal(items, targetInputId, callback) {
    ipModalTarget = targetInputId; ipModalCallback = callback;
    let modal = document.getElementById('imagePickerModal');
    if (!modal) { modal = document.createElement('div'); modal.id = 'imagePickerModal'; modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9000;display:flex;align-items:center;justify-content:center;padding:20px;'; document.body.appendChild(modal); }
    modal.innerHTML = `<div style="background:#fff;border-radius:14px;width:100%;max-width:700px;max-height:80vh;display:flex;flex-direction:column;overflow:hidden;"><div style="background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;padding:14px 20px;font-weight:700;display:flex;justify-content:space-between;align-items:center;"><span>🖼️ Select Image</span><button onclick="document.getElementById('imagePickerModal').style.display='none'" style="background:rgba(255,255,255,0.2);color:#fff;border:none;width:32px;height:32px;border-radius:50%;font-size:18px;cursor:pointer;">✕</button></div><div style="flex:1;overflow-y:auto;padding:16px;display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:10px;">${items.map(item => `<div onclick="pickImage('${item.url}')" style="border:2px solid #e2e8f0;border-radius:8px;overflow:hidden;cursor:pointer;transition:all .15s;" onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#e2e8f0'"><img src="${item.thumb_url || item.url}" style="width:100%;height:90px;object-fit:cover;"><div style="padding:4px 6px;font-size:10px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${item.name}</div></div>`).join('')}</div></div>`;
    modal.style.display = 'flex';
}
function pickImage(url) {
    const modal = document.getElementById('imagePickerModal');
    if (modal) modal.style.display = 'none';
    if (ipModalTarget) { const input = document.getElementById(ipModalTarget); if (input) input.value = url; }
    if (ipModalCallback) ipModalCallback(url);
    ipModalTarget = null; ipModalCallback = null;
}

function toggleEdit(qid) { const form = document.getElementById('edit-form-' + qid); form.style.display = form.style.display === 'none' ? 'block' : 'none'; }
</script>
@endsection