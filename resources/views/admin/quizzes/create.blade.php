@extends('admin.layouts.app')
@section('title', 'New Quiz — Full Builder')
@section('content')

<style>
.qb-builder{display:flex;flex-direction:column;gap:18px;}
.qb-section{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;}
.qb-section-header{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:12px 18px;font-weight:700;font-size:14px;display:flex;align-items:center;gap:8px;}
.qb-section-body{padding:18px;}
.qb-question-card{border:2px solid #e2e8f0;border-radius:10px;margin-bottom:14px;background:#f8fafc;}
.qb-question-head{display:flex;justify-content:space-between;align-items:center;padding:10px 14px;background:#eef2ff;border-radius:8px 8px 0 0;}
.qb-question-num{font-weight:700;color:#4f46e5;font-size:13px;display:flex;align-items:center;gap:6px;}
.qb-question-body{padding:14px;}
.qb-option-row{display:flex;gap:8px;align-items:flex-start;margin-bottom:6px;background:#fff;padding:8px 10px;border:1px solid #e2e8f0;border-radius:6px;}
.qb-type-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;margin-top:8px;}
.qb-type-card{border:2px solid #e2e8f0;border-radius:8px;padding:10px;cursor:pointer;text-align:center;transition:all .15s;font-size:12px;background:#fff;}
.qb-type-card:hover{border-color:#a5b4fc;background:#eef2ff;}
.qb-type-card.selected{border-color:#4f46e5;background:#eef2ff;}
.qb-type-card .icon{font-size:22px;display:block;margin-bottom:4px;}
.qb-type-card .label{font-weight:600;color:#334155;line-height:1.2;}
.qb-add-question-btn{border:2px dashed #a5b4fc;background:#f8fafc;color:#4f46e5;padding:12px;border-radius:10px;cursor:pointer;font-weight:600;width:100%;font-size:14px;transition:all .15s;}
.qb-add-question-btn:hover{background:#eef2ff;border-color:#6366f1;}
.qb-remove-btn{background:#ef4444;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;}
.qb-add-option-btn{background:#10b981;color:#fff;border:none;border-radius:6px;padding:5px 12px;font-size:12px;cursor:pointer;margin-top:4px;}
.no-options-type{background:#fef3c7;border:1px solid #fcd34d;color:#92400e;padding:8px 12px;border-radius:6px;font-size:12px;margin-top:8px;}
.qb-options-help{font-size:11px;color:#6b7280;background:#f0f9ff;border-left:3px solid #0ea5e9;padding:6px 10px;border-radius:4px;margin-bottom:8px;}
.qb-ctype-toggle{display:inline-flex;border:1px solid #cbd5e1;border-radius:6px;overflow:hidden;}
.qb-ctype-toggle button{background:#fff;border:none;padding:3px 8px;cursor:pointer;font-size:11px;color:#64748b;transition:all .15s;}
.qb-ctype-toggle button.active{background:#4f46e5;color:#fff;font-weight:600;}
.qb-img-preview{width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid #e2e8f0;}
.qb-img-placeholder{width:44px;height:44px;display:flex;align-items:center;justify-content:center;border-radius:6px;border:1px dashed #cbd5e1;color:#cbd5e1;font-size:20px;}
.qb-media-btn{background:#8b5cf6;color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:12px;cursor:pointer;white-space:nowrap;font-weight:600;}
.qb-media-btn.is-set{background:#22c55e;}
.qb-prompt-preview{margin-top:6px;padding:6px;border:1px solid #e2e8f0;border-radius:6px;display:flex;align-items:center;gap:8px;}
.qb-prompt-preview img{width:64px;height:64px;object-fit:cover;border-radius:6px;}
/* Media Picker */
.mp-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9000;display:flex;align-items:center;justify-content:center;padding:20px;}
.mp-modal{background:#fff;border-radius:14px;width:100%;max-width:900px;max-height:85vh;display:flex;flex-direction:column;overflow:hidden;}
.mp-header{background:linear-gradient(135deg,#8b5cf6,#6366f1);color:#fff;padding:14px 20px;font-weight:700;display:flex;justify-content:space-between;align-items:center;}
.mp-close{background:rgba(255,255,255,0.2);color:#fff;border:none;width:32px;height:32px;border-radius:50%;font-size:18px;cursor:pointer;}
.mp-filters{padding:14px 20px;border-bottom:1px solid #e2e8f0;display:flex;gap:10px;flex-wrap:wrap;align-items:center;background:#f8fafc;}
.mp-filters input[type=text],.mp-filters select{padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;}
.mp-grid{flex:1;overflow-y:auto;padding:16px 20px;display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:12px;align-content:start;}
.mp-empty{grid-column:1/-1;text-align:center;padding:40px;color:#94a3b8;}
.mp-item{border:2px solid #e2e8f0;border-radius:10px;overflow:hidden;cursor:pointer;transition:all .15s;background:#fff;}
.mp-item:hover{border-color:#8b5cf6;transform:translateY(-2px);}
.mp-item.selected{border-color:#22c55e;background:#f0fdf4;}
.mp-thumb{width:100%;height:100px;object-fit:cover;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:32px;color:#cbd5e1;}
.mp-info{padding:6px 8px;}
.mp-name{font-size:11px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.mp-meta{font-size:9px;color:#94a3b8;}
.mp-footer{padding:12px 20px;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;background:#f8fafc;}
.mp-loading{text-align:center;padding:30px;color:#94a3b8;}
/* PAIR EDITOR */
.qb-pair-row{display:grid;grid-template-columns:28px 1fr 30px 1fr 32px;gap:6px;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:8px;margin-bottom:6px;}
.qb-pair-arrow{text-align:center;font-size:16px;color:#8b5cf6;font-weight:700;}
.qb-pair-slot{border:2px dashed #cbd5e1;border-radius:6px;padding:6px;min-height:60px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;gap:4px;}
.qb-pair-slot img{max-width:100%;max-height:48px;border-radius:4px;}
.qb-pair-num{background:#eef2ff;color:#4f46e5;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;}
/* BUCKET EDITOR */
.qb-bucket-def{display:grid;grid-template-columns:36px 1fr 50px 32px;gap:6px;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:6px;margin-bottom:4px;}
.qb-bucket-color{width:32px;height:32px;border-radius:6px;border:2px solid #cbd5e1;cursor:pointer;}
/* HOTSPOT EDITOR */
.qb-hotspot-editor{position:relative;display:inline-block;max-width:100%;margin-top:8px;border:2px solid #e2e8f0;border-radius:8px;overflow:hidden;background:#000;cursor:crosshair;}
.qb-hotspot-editor img{display:block;max-width:100%;}
.qb-hotspot-dot{position:absolute;border:3px solid #22c55e;background:rgba(34,197,94,0.3);border-radius:50%;cursor:pointer;transform:translate(-50%,-50%);width:36px;height:36px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;text-shadow:0 1px 2px rgba(0,0,0,0.8);}
.qb-hotspot-dot:hover{background:rgba(239,68,68,0.4);border-color:#ef4444;}
.qb-hotspot-empty{padding:40px;text-align:center;color:#94a3b8;background:#f8fafc;border:2px dashed #cbd5e1;border-radius:8px;}
/* TRACING */
.qb-tracing-preview{border:2px solid #e2e8f0;border-radius:8px;padding:8px;background:#fef3c7;text-align:center;margin-top:8px;}
.qb-tracing-preview img{max-width:200px;max-height:200px;opacity:0.5;}
/* BADGE */
.qb-landscape-badge{display:inline-flex;align-items:center;gap:4px;background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:600;margin-left:6px;}
/* NEW: Enhanced question fields */
.qb-enhanced-section{background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:10px;margin-top:10px;}
.qb-enhanced-label{font-size:11px;font-weight:700;color:#7c3aed;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;display:flex;align-items:center;gap:4px;}
.qb-narration-box{background:#fff;border:1px solid #e9d5ff;border-radius:6px;padding:8px;margin-top:4px;}
.qb-narration-box textarea{font-size:13px;line-height:1.4;color:#4c1d95;}
.qb-voice-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:16px;border:2px solid #e2e8f0;background:#fff;cursor:pointer;font-size:12px;font-weight:600;transition:all .15s;color:#64748b;}
.qb-voice-pill.active{border-color:#8b5cf6;background:#f3e8ff;color:#6d28d9;}
.qb-voice-pill:hover{border-color:#a5b4fc;}
.qb-dup-btn{background:#8b5cf6;color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:12px;cursor:pointer;font-weight:600;margin-left:6px;}
.qb-dup-btn:hover{background:#7c3aed;}
.qb-dup-opt-btn{background:#f1f5f9;color:#6366f1;border:1px solid #c7d2fe;border-radius:6px;padding:4px 8px;font-size:11px;cursor:pointer;font-weight:600;white-space:nowrap;}
.qb-dup-opt-btn:hover{background:#eef2ff;border-color:#818cf8;}
.qb-multi-img-grid{display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;}
.qb-multi-img-item{position:relative;width:60px;height:60px;border-radius:6px;overflow:hidden;border:2px solid #e2e8f0;}
.qb-multi-img-item img{width:100%;height:100%;object-fit:cover;}
.qb-multi-img-remove{position:absolute;top:0;right:0;background:rgba(239,68,68,0.9);color:#fff;border:none;border-radius:0 0 0 6px;padding:2px 4px;font-size:9px;cursor:pointer;}
.qb-add-img-btn{width:60px;height:60px;border:2px dashed #c7d2fe;border-radius:6px;background:#faf5ff;color:#8b5cf6;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:20px;}
.qb-add-img-btn:hover{background:#f3e8ff;border-color:#8b5cf6;}
/* Image + Text combined option */
.qb-opt-combined{display:grid;grid-template-columns:60px 1fr;gap:8px;align-items:start;}
.qb-opt-thumb{width:60px;height:60px;border-radius:6px;border:2px dashed #cbd5e1;display:flex;align-items:center;justify-content:center;font-size:18px;color:#cbd5e1;cursor:pointer;overflow:hidden;}
.qb-opt-thumb img{width:100%;height:100%;object-fit:cover;}
</style>

{{-- Media Picker Modal --}}
<div id="mediaPickerOverlay" class="mp-overlay" style="display:none;" onclick="if(event.target===this)closeMediaPicker()">
    <div class="mp-modal">
        <div class="mp-header">
            <span id="mpTitle">🖼️ Media Library</span>
            <button type="button" class="mp-close" onclick="closeMediaPicker()">✕</button>
        </div>
        <div class="mp-filters">
            <input type="text" id="mpSearch" placeholder="🔍 Search…" oninput="debouncedSearch()" style="flex:1;min-width:200px;">
            <select id="mpSubject" onchange="loadMediaItems()">
                <option value="">All Subjects</option>
                @foreach ($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <span style="font-size:11px;color:#94a3b8;" id="mpCount">Loading…</span>
        </div>
        <div id="mpGrid" class="mp-grid"><div class="mp-loading">Loading…</div></div>
        <div class="mp-footer">
            <button type="button" class="qb-media-btn" style="background:#94a3b8;" onclick="clearMediaSelection()">✕ Clear</button>
            <span style="font-size:12px;color:#64748b;">Click item to select, then click anywhere outside to apply</span>
        </div>
    </div>
</div>
<script>window.MEDIA_SEARCH_URL = '{{ route("admin.media.search") }}';</script>

<form action="{{ route('admin.quizzes.store') }}" method="POST" id="quizForm">
@csrf
<div class="qb-builder">

    {{-- Quiz Details --}}
    <div class="qb-section">
        <div class="qb-section-header">📋 Quiz Details</div>
        <div class="qb-section-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Lesson <span style="color:#dc3545;">*</span></label>
                    <select name="lesson_id" class="form-control" required>
                        <option value="">Select a lesson…</option>
                        @foreach ($lessons as $lesson)
                            <option value="{{ $lesson->id }}" @if(old('lesson_id') == $lesson->id) selected @endif>
                                {{ $lesson->title }} — {{ $lesson->topic->subject->name ?? '?' }} / {{ $lesson->topic->name ?? '?' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quiz Title <span style="color:#dc3545;">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="e.g., Letter A — Quick Check" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Instructions (shown to child)</label>
                <textarea name="instructions" class="form-control" rows="2" placeholder="e.g., Tap the right answer!">{{ old('instructions') }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Pass Threshold (%)</label>
                    <input type="number" name="pass_threshold_percent" class="form-control" value="{{ old('pass_threshold_percent', 70) }}" min="0" max="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Attempts</label>
                    <input type="number" name="max_attempts" class="form-control" value="{{ old('max_attempts', 3) }}" min="1" max="10">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="draft" @if(old('status','draft') == 'draft') selected @endif>Draft</option>
                        <option value="in_review" @if(old('status') == 'in_review') selected @endif>In Review</option>
                        <option value="published" @if(old('status') == 'published') selected @endif>Published</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:24px;margin-top:4px;">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="shuffle_questions" {{ old('shuffle_questions') ? 'checked' : '' }}> Shuffle questions
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                    <input type="checkbox" name="shuffle_options" {{ old('shuffle_options') ? 'checked' : '' }}> Shuffle options
                </label>
            </div>
        </div>
    </div>

    {{-- Questions Builder --}}
    <div class="qb-section">
        <div class="qb-section-header">🎯 Questions Builder — Build everything on one page!</div>
        <div class="qb-section-body">
            <script>window.QUIZ_TYPES = @json($quizTypes);</script>
            <div id="questionsContainer"></div>
            <button type="button" class="qb-add-question-btn" onclick="addQuestion()">➕ Add New Question</button>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;">
        <a href="{{ route('admin.quizzes.index') }}" class="btn btn-secondary">← Cancel</a>
        <button type="submit" class="btn btn-primary" style="font-size:15px;padding:10px 28px;">
            💾 Create Quiz with <span id="qCountDisplay">0</span> Question(s)
        </button>
    </div>
</div>
</form>

<script>
let questionIndex = 0;
let optionIndex = {};
let selectedTypes = {};
let pairIndex = {};
let bucketIndex = {};
let bucketItemIndex = {};
let hotspotIndex = {};
const QUIZ_TYPES = window.QUIZ_TYPES || [];
const MEDIA_OPTION_TYPES = ['QT-01','QT-03','QT-04','QT-05','QT-06','QT-10','QT-11'];

function getType(typeId) { return QUIZ_TYPES.find(t => t.id == typeId); }

function typeGuidance(code) {
    const map = {
        'QT-01': { header: '📝 Answer Options', help: 'Child taps the correct answer. Mark it with ✅. Use 📝/🖼️ toggle for image options.' },
        'QT-02': { header: '📝 True / False', help: 'Two options. Mark the correct one.' },
        'QT-03': { header: '🔗 Matching Pairs', help: 'Create left↔right pairs. Items in the same row are a match.' },
        'QT-04': { header: '📦 Sort by Category', help: 'Define category buckets, then add items assigned to each bucket.' },
        'QT-05': { header: '🔢 Sequence / Order', help: 'List items in correct order. Match Key = position (1, 2, 3…).' },
        'QT-06': { header: '🔊 Listen & Tap', help: 'Child hears audio then taps the answer. Add audio URL above.' },
        'QT-07': { header: '🎤 Voice Activity', help: 'No options — child speaks aloud. Add audio URL of the word.' },
        'QT-08': { header: '✏️ Spell / Fill', help: 'List letter tiles. Mark the correct letter with ✅.' },
        'QT-09': { header: '🔢 Count & Choose', help: 'Add number options. Mark the correct count. Add a counting image above.' },
        'QT-10': { header: '🔁 Complete the Pattern', help: 'List possible next items. Mark the correct one.' },
        'QT-11': { header: '🃏 Memory Match', help: 'Create card pairs. Items in the same row form a pair.' },
        'QT-12': { header: '✍️ Tracing', help: 'Upload a dashed/outline PNG above. Child traces over it.' },
        'QT-13': { header: '🔍 Spot & Find', help: 'Upload a 16:9 scene image above, then click on it to add hotspots.' },
    };
    return map[code] || { header: '📝 Options', help: '' };
}

// ════════════════════════════════════════════════════════════════
// ADD QUESTION
// ════════════════════════════════════════════════════════════════
function addQuestion() {
    const idx = questionIndex;
    const container = document.getElementById('questionsContainer');
    const card = document.createElement('div');
    card.className = 'qb-question-card';
    card.id = `question-${idx}`;

    let typeCardsHtml = '';
    QUIZ_TYPES.forEach(t => {
        typeCardsHtml += `<div class="qb-type-card" onclick="selectType(${idx}, ${t.id}, this)" data-type-id="${t.id}" title="${(t.description||'').replace(/"/g,'"')}"><span class="icon">${t.icon||'❓'}</span><span class="label">${t.name}</span></div>`;
    });

    card.innerHTML = `
        <div class="qb-question-head">
            <span class="qb-question-num">📌 Question <span class="qNum">${idx+1}</span></span>
            <div>
                <button type="button" class="qb-dup-btn" onclick="duplicateQuestion(${idx})">📋 Duplicate</button>
                <button type="button" class="qb-remove-btn" onclick="removeQuestion(${idx})">🗑 Remove</button>
            </div>
        </div>
        <div class="qb-question-body">
            <input type="hidden" name="questions[${idx}][quiz_type_id]" id="typeInput-${idx}" value="">
            <input type="hidden" name="questions[${idx}][sort_order]" value="${idx}">
            <input type="hidden" name="questions[${idx}][metadata]" id="metadata-${idx}" value="">
            <input type="hidden" name="questions[${idx}][additional_images]" id="additionalImages-${idx}" value="">
            <label class="form-label" style="font-size:13px;font-weight:700;">Choose Question Type:</label>
            <div class="qb-type-grid">${typeCardsHtml}</div>
            <div id="selectedTypeLabel-${idx}" style="margin-top:6px;font-size:12px;font-weight:700;color:#4f46e5;"></div>
            <div style="margin-top:12px;">
                <label class="form-label" style="font-size:13px;font-weight:700;">Question Prompt <span style="color:#dc3545;">*</span></label>
                <textarea name="questions[${idx}][prompt]" class="form-control" rows="2" placeholder="e.g., Which word starts with A?" required></textarea>
            </div>

            <div class="form-group" style="margin-top:12px; margin-bottom:12px;">
                <label class="form-label" style="font-size:11px; color:#64748b;">🗣️ TTS Override (Hidden reading text)</label>
                <input type="text" name="questions[${idx}][narration_text]" class="form-control" placeholder="If the prompt is 'C _ T', type 'Cat' here so the engine reads it properly!" style="font-size:12px; border-color:#e2e8f0; background:#f8fafc;" oninput="updateVoicePreviewCreate(${idx})">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-top:10px;">
                <div>
                    <label class="form-label" style="font-size:12px;">Points</label>
                    <input type="number" name="questions[${idx}][points]" class="form-control" value="1" min="1" max="10" style="font-size:13px;">
                </div>
                <div>
                    <label class="form-label" style="font-size:12px;">Difficulty</label>
                    <select name="questions[${idx}][difficulty]" class="form-control" style="font-size:13px;">
                        <option value="">— Select —</option>
                        <option value="easy">🟢 Easy</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="hard">🔴 Hard</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" style="font-size:12px;">CBC Outcome Code</label>
                    <input type="text" name="questions[${idx}][cbc_outcome_code]" class="form-control" placeholder="e.g., ECDE-1.2.3" style="font-size:13px;">
                </div>
            </div>
            <div id="imageRow-${idx}" style="margin-top:10px;">
                <label class="form-label" style="font-size:12px;">🖼️ Question Image(s)</label>
                <div style="display:flex;gap:4px;">
                    <input type="text" name="questions[${idx}][prompt_image_url]" id="promptImg-${idx}" class="form-control" placeholder="Primary image (auto-filled)" style="flex:1;font-size:13px;" readonly>
                    <button type="button" class="qb-media-btn" onclick="openMediaPicker('prompt', ${idx}, 'image')">🖼️ Browse</button>
                </div>
                <div id="promptImgPreview-${idx}" class="qb-prompt-preview" style="display:none;"></div>
                <div class="qb-multi-img-grid" id="multiImgGrid-${idx}"></div>
                <button type="button" class="qb-dup-opt-btn" style="margin-top:4px;" onclick="addAdditionalImage(${idx})">➕ Add more images</button>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:8px;">
                <div><label class="form-label" style="font-size:12px;">Hint (optional)</label><input type="text" name="questions[${idx}][hint]" class="form-control" placeholder="Think of…" style="font-size:13px;"></div>
                <div><label class="form-label" style="font-size:12px;">Explanation (optional)</label><input type="text" name="questions[${idx}][explanation]" class="form-control" placeholder="Because…" style="font-size:13px;"></div>
            </div>
            <div style="margin-top:8px;display:none;" id="audioRow-${idx}">
                <label class="form-label" style="font-size:12px;">🔊 Audio URL (Manual upload)</label>
                <div style="display:flex;gap:4px;">
                    <input type="text" name="questions[${idx}][prompt_audio_url]" id="promptAud-${idx}" class="form-control" placeholder="Auto-filled" style="flex:1;font-size:13px;" readonly>
                    <button type="button" class="qb-media-btn" onclick="openMediaPicker('prompt', ${idx}, 'audio')">🔊 Browse</button>
                </div>
            </div>
            <div class="qb-enhanced-section">
                <div class="qb-enhanced-label">🎙️ Voice Profile (TTS fallback — the Prompt is spoken with this voice when no Prompt Audio is uploaded)</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;" id="voicePills-${idx}">
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'leo', this)">🦁 Leo</span>
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'lily', this)">🦋 Lily</span>
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'max', this)">🐻 Max</span>
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'mia', this)">🐰 Mia</span>
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'teacher', this)">👩‍🏫 Teacher</span>
                    <span class="qb-voice-pill" onclick="selectVoice(${idx}, 'custom', this)">⚙️ Custom</span>
                </div>
                <input type="hidden" name="questions[${idx}][voice_profile]" id="voiceInput-${idx}" value="">
                <div class="qb-custom-voice-input" id="customVoice-${idx}" style="display:none;margin-top:6px;">
                    <input type="text" placeholder="Enter custom voice name…" oninput="document.getElementById('voiceInput-${idx}').value = this.value; updateVoicePreviewCreate(${idx});" style="font-size:12px;padding:4px 8px;border:1px solid #c7d2fe;border-radius:6px;width:200px;">
                </div>
                <div id="voicePreview-${idx}" class="qb-narration-preview" style="display:none;margin-top:8px;">
                    <span class="voice-tag" id="voicePreviewTag-${idx}">—</span>
                    <div class="prompt-preview" id="voicePreviewPrompt-${idx}"></div>
                </div>
            </div>
            <div id="optionsContainer-${idx}" style="margin-top:14px;"></div>
            <div id="optionsNotice-${idx}"></div>
        </div>`;
    container.appendChild(card);
    card.style.opacity = '0';
    card.style.transition = 'opacity 0.2s';
    requestAnimationFrame(() => { card.style.opacity = '1'; });
    questionIndex++;
    updateCounter();
    setTimeout(() => card.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
}

// ════════════════════════════════════════════════════════════════
// SELECT TYPE — routes to specialized editors
// ════════════════════════════════════════════════════════════════
function selectType(idx, typeId, el) {
    const type = getType(typeId);
    if (!type) return;
    document.querySelectorAll(`#question-${idx} .qb-type-card`).forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById(`typeInput-${idx}`).value = typeId;
    selectedTypes[idx] = type;
    const code = type.code;
    document.getElementById(`selectedTypeLabel-${idx}`).innerHTML = `✅ ${type.icon||'❓'} ${type.name}`;

    document.getElementById(`audioRow-${idx}`).style.display = (code === 'QT-06' || code === 'QT-07') ? 'block' : 'none';
    document.getElementById(`imageRow-${idx}`).style.display = 'block';

    const oc = document.getElementById(`optionsContainer-${idx}`);
    const on = document.getElementById(`optionsNotice-${idx}`);
    oc.innerHTML = '';
    on.innerHTML = '';
    const g = typeGuidance(code);

    // ── Specialized editors ──
    if (code === 'QT-03' || code === 'QT-11') { renderPairEditor(idx, oc, g); return; }
    if (code === 'QT-04') { renderBucketEditor(idx, oc, g); return; }
    if (code === 'QT-13') { renderHotspotEditor(idx, oc, g); return; }
    if (code === 'QT-12') { renderTracingNotice(idx, oc, g); return; }

    // ── Generic option editor ──
    if (!type.has_options) {
        on.innerHTML = `<div class="no-options-type">ℹ️ <strong>${type.name}</strong> — ${g.help || 'No answer options needed.'}</div>`;
        return;
    }
    const header = document.createElement('div');
    header.innerHTML = `<div style="display:flex;justify-content:space-between;align-items:center;"><span style="font-size:13px;font-weight:700;color:#334155;">${g.header}</span><button type="button" class="qb-add-option-btn" onclick="addOption(${idx})">➕ Add Option</button></div>${g.help?`<div class="qb-options-help">${g.help}</div>`:''}`;
    oc.appendChild(header);

    if (code === 'QT-02') { addOption(idx,{text:'True',correct:true}); addOption(idx,{text:'False'}); }
    else if (code === 'QT-05') { addOption(idx,{matchKey:'1'}); addOption(idx,{matchKey:'2'}); addOption(idx,{matchKey:'3'}); }
    else { addOption(idx); addOption(idx); }
}

// ════════════════════════════════════════════════════════════════
// PAIR EDITOR (QT-03 Matching + QT-11 Memory)
// ════════════════════════════════════════════════════════════════
function renderPairEditor(qIdx, container, g) {
    const isMemory = selectedTypes[qIdx].code === 'QT-11';
    container.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:700;color:#334155;">${isMemory?'🃏 Card Pairs':'🔗 Matching Pairs'}</span>
            <span class="qb-landscape-badge">📱 Left ↔ Right</span>
        </div>
        <div class="qb-options-help">${isMemory?'Each row = 2 cards that match. Flip to find pairs.':'Child draws lines left→right. Same row = a match.'}</div>
        <div id="pairsContainer-${qIdx}"></div>
        <button type="button" class="qb-add-option-btn" onclick="addPair(${qIdx})">➕ Add Pair</button>`;
    addPair(qIdx);
    addPair(qIdx);
}

function addPair(qIdx) {
    if (pairIndex[qIdx] === undefined) pairIndex[qIdx] = 0;
    const pIdx = pairIndex[qIdx];
    const container = document.getElementById(`pairsContainer-${qIdx}`);
    const pairKey = `pair_${pIdx+1}`;
    const row = document.createElement('div');
    row.className = 'qb-pair-row';
    row.id = `pair-${qIdx}-${pIdx}`;
    row.innerHTML = `
        <div class="qb-pair-num">${pIdx+1}</div>
        <div class="qb-pair-slot">
            <div class="qb-ctype-toggle">
                <button type="button" class="active" onclick="changePairType(${qIdx},${pIdx},'left','text',this)">📝</button>
                <button type="button" onclick="changePairType(${qIdx},${pIdx},'left','image',this)">🖼️</button>
            </div>
            <input type="text" name="questions[${qIdx}][options][L${pIdx}][text_value]" class="form-control" placeholder="🍎 or 'Apple'" style="font-size:12px;">
            <input type="hidden" name="questions[${qIdx}][options][L${pIdx}][content_type]" value="text">
            <input type="hidden" name="questions[${qIdx}][options][L${pIdx}][match_key]" value="${pairKey}">
            <input type="hidden" name="questions[${qIdx}][options][L${pIdx}][sort_order]" value="${pIdx*2}">
            <input type="hidden" name="questions[${qIdx}][options][L${pIdx}][image_url]" id="pair-${qIdx}-${pIdx}-L-img" value="">
            <input type="hidden" name="questions[${qIdx}][options][L${pIdx}][is_correct]" value="0">
            <button type="button" class="qb-media-btn" style="display:none;font-size:10px;padding:4px 8px;margin-top:4px;" id="pair-${qIdx}-${pIdx}-L-browse" onclick="openPairPicker(${qIdx},${pIdx},'L')">🖼️ Browse</button>
            <div id="pair-${qIdx}-${pIdx}-L-preview" style="display:none;margin-top:4px;"></div>
        </div>
        <div class="qb-pair-arrow">↔</div>
        <div class="qb-pair-slot">
            <div class="qb-ctype-toggle">
                <button type="button" class="active" onclick="changePairType(${qIdx},${pIdx},'right','text',this)">📝</button>
                <button type="button" onclick="changePairType(${qIdx},${pIdx},'right','image',this)">🖼️</button>
            </div>
            <input type="text" name="questions[${qIdx}][options][R${pIdx}][text_value]" class="form-control" placeholder="'A' or 🖼️" style="font-size:12px;">
            <input type="hidden" name="questions[${qIdx}][options][R${pIdx}][content_type]" value="text">
            <input type="hidden" name="questions[${qIdx}][options][R${pIdx}][match_key]" value="${pairKey}">
            <input type="hidden" name="questions[${qIdx}][options][R${pIdx}][sort_order]" value="${pIdx*2+1}">
            <input type="hidden" name="questions[${qIdx}][options][R${pIdx}][image_url]" id="pair-${qIdx}-${pIdx}-R-img" value="">
            <input type="hidden" name="questions[${qIdx}][options][R${pIdx}][is_correct]" value="1">
            <button type="button" class="qb-media-btn" style="display:none;font-size:10px;padding:4px 8px;margin-top:4px;" id="pair-${qIdx}-${pIdx}-R-browse" onclick="openPairPicker(${qIdx},${pIdx},'R')">🖼️ Browse</button>
            <div id="pair-${qIdx}-${pIdx}-R-preview" style="display:none;margin-top:4px;"></div>
        </div>
        <button type="button" onclick="document.getElementById('pair-${qIdx}-${pIdx}').remove()" style="background:#94a3b8;color:#fff;border:none;border-radius:4px;padding:6px;cursor:pointer;font-size:12px;">✕</button>`;
    container.appendChild(row);
    pairIndex[qIdx]++;
}

function changePairType(qIdx, pIdx, side, newType, btn) {
    const slot = btn.closest('.qb-pair-slot');
    const textInput = slot.querySelector('input[type=text]');
    const browseBtn = document.getElementById(`pair-${qIdx}-${pIdx}-${side[0].toUpperCase()}-browse`);
    const ctypeInput = slot.querySelector('input[name*="[content_type]"]');
    ctypeInput.value = newType;
    slot.querySelectorAll('.qb-ctype-toggle button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    textInput.style.display = newType === 'text' ? 'block' : 'none';
    if (browseBtn) browseBtn.style.display = newType === 'image' ? 'inline-block' : 'none';
}

function openPairPicker(qIdx, pIdx, side) {
    mpContext = { kind: 'pair', qIdx, pairIdx: pIdx, pairSide: side, mediaType: 'image' };
    mpSelectedUrl = null;
    document.getElementById('mpTitle').textContent = `🖼️ Select image for ${side === 'L' ? 'left' : 'right'} side`;
    document.getElementById('mpSearch').value = '';
    document.getElementById('mpSubject').value = '';
    document.getElementById('mediaPickerOverlay').style.display = 'flex';
    loadMediaItems();
}

// ════════════════════════════════════════════════════════════════
// BUCKET EDITOR (QT-04 Sort)
// ════════════════════════════════════════════════════════════════
function renderBucketEditor(qIdx, container, g) {
    container.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:700;color:#334155;">📦 Category Buckets</span>
            <span class="qb-landscape-badge">📱 Buckets on top, items below</span>
        </div>
        <div class="qb-options-help">Step 1: Define categories (buckets). Step 2: Add items and assign each to a bucket.</div>
        <div id="bucketsContainer-${qIdx}"></div>
        <button type="button" class="qb-add-option-btn" style="margin-bottom:12px;" onclick="addBucket(${qIdx})">➕ Add Bucket</button>
        <div style="font-size:13px;font-weight:700;color:#334155;margin-top:10px;">🎯 Items to Sort</div>
        <div id="bucketItemsContainer-${qIdx}"></div>
        <button type="button" class="qb-add-option-btn" onclick="addBucketItem(${qIdx})">➕ Add Sort Item</button>`;
    addBucket(qIdx, 'Farm', '🐄', '#10b981');
    addBucket(qIdx, 'Wild', '🦁', '#f59e0b');
    addBucketItem(qIdx);
    addBucketItem(qIdx);
}

function addBucket(qIdx, name, icon, color) {
    if (bucketIndex[qIdx] === undefined) bucketIndex[qIdx] = 0;
    const bIdx = bucketIndex[qIdx];
    const container = document.getElementById(`bucketsContainer-${qIdx}`);
    const bucketKey = `bucket_${bIdx}`;
    const row = document.createElement('div');
    row.className = 'qb-bucket-def';
    row.id = `bucket-${qIdx}-${bIdx}`;
    row.innerHTML = `
        <input type="color" value="${color}" class="qb-bucket-color">
        <input type="text" class="form-control" value="${name}" placeholder="Category" style="font-size:13px;" oninput="refreshBucketSelects(${qIdx})">
        <input type="text" class="form-control" value="${icon}" placeholder="🐂" maxlength="2" style="font-size:18px;text-align:center;">
        <button type="button" onclick="removeBucket(${qIdx},${bIdx})" style="background:#94a3b8;color:#fff;border:none;border-radius:4px;padding:6px;cursor:pointer;">✕</button>
        <input type="hidden" name="questions[${qIdx}][buckets][${bIdx}][key]" value="${bucketKey}">`;
    container.appendChild(row);
    bucketIndex[qIdx]++;
    refreshBucketSelects(qIdx);
    saveBucketMetadata(qIdx);
    // Save bucket metadata on any change
    row.addEventListener('input', () => saveBucketMetadata(qIdx));
}

function removeBucket(qIdx, bIdx) {
    document.getElementById(`bucket-${qIdx}-${bIdx}`).remove();
    refreshBucketSelects(qIdx);
    saveBucketMetadata(qIdx);
}

function getBuckets(qIdx) {
    const buckets = [];
    document.querySelectorAll(`#bucketsContainer-${qIdx} .qb-bucket-def`).forEach(row => {
        const inputs = row.querySelectorAll('input');
        const color = inputs[0].value;
        const name = inputs[1].value;
        const icon = inputs[2].value;
        const key = row.querySelector('input[type=hidden]').value;
        if (name.trim()) buckets.push({ key, name, icon, color });
    });
    return buckets;
}

function refreshBucketSelects(qIdx) {
    const buckets = getBuckets(qIdx);
    document.querySelectorAll(`#bucketItemsContainer-${qIdx} select`).forEach(sel => {
        const current = sel.value;
        sel.innerHTML = '<option value="">Assign…</option>' + buckets.map(b => `<option value="${b.key}" ${current===b.key?'selected':''}>${b.icon} ${b.name}</option>`).join('');
    });
}

function addBucketItem(qIdx) {
    if (bucketItemIndex[qIdx] === undefined) bucketItemIndex[qIdx] = 0;
    const iIdx = bucketItemIndex[qIdx];
    const container = document.getElementById(`bucketItemsContainer-${qIdx}`);
    const buckets = getBuckets(qIdx);
    const row = document.createElement('div');
    row.className = 'qb-option-row';
    row.id = `bitem-${qIdx}-${iIdx}`;
    row.innerHTML = `
        <input type="hidden" name="questions[${qIdx}][options][${iIdx}][content_type]" value="text">
        <input type="hidden" name="questions[${qIdx}][options][${iIdx}][image_url]" id="bitem-${qIdx}-${iIdx}-img" value="">
        <div class="qb-ctype-toggle">
            <button type="button" class="active" onclick="changeBucketItemType(${qIdx},${iIdx},'text',this)">📝</button>
            <button type="button" onclick="changeBucketItemType(${qIdx},${iIdx},'image',this)">🖼️</button>
        </div>
        <input type="text" name="questions[${qIdx}][options][${iIdx}][text_value]" class="form-control" placeholder="Item name…" style="flex:1;font-size:13px;">
        <button type="button" class="qb-media-btn" style="display:none;" id="bitem-${qIdx}-${iIdx}-browse" onclick="openBucketItemPicker(${qIdx},${iIdx})">🖼️</button>
        <div id="bitem-${qIdx}-${iIdx}-preview" style="display:none; margin-right: 8px;"></div>
        <select name="questions[${qIdx}][options][${iIdx}][match_key]" class="form-control" style="width:auto;font-size:12px;">
            <option value="">Assign…</option>
            ${buckets.map(b => `<option value="${b.key}">${b.icon} ${b.name}</option>`).join('')}
        </select>
        <button type="button" onclick="document.getElementById('bitem-${qIdx}-${iIdx}').remove()" style="background:#94a3b8;color:#fff;border:none;border-radius:4px;padding:5px;cursor:pointer;font-size:12px;">✕</button>`;
    container.appendChild(row);
    bucketItemIndex[qIdx]++;
}

function changeBucketItemType(qIdx, iIdx, newType, btn) {
    const row = document.getElementById(`bitem-${qIdx}-${iIdx}`);
    const textInput = row.querySelector('input[name*="text_value"]');
    const browseBtn = document.getElementById(`bitem-${qIdx}-${iIdx}-browse`);
    const ctypeInput = row.querySelector('input[name*="content_type"]');
    ctypeInput.value = newType;
    row.querySelectorAll('.qb-ctype-toggle button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    textInput.style.display = newType === 'text' ? 'block' : 'none';
    if (browseBtn) browseBtn.style.display = newType === 'image' ? 'inline-block' : 'none';
}

function openBucketItemPicker(qIdx, iIdx) {
    mpContext = { kind: 'bucket-item', qIdx, itemIdx: iIdx, mediaType: 'image' };
    mpSelectedUrl = null;
    document.getElementById('mpTitle').textContent = '🖼️ Select item image';
    document.getElementById('mpSearch').value = '';
    document.getElementById('mpSubject').value = '';
    document.getElementById('mediaPickerOverlay').style.display = 'flex';
    loadMediaItems();
}

function saveBucketMetadata(qIdx) {
    const metaInput = document.getElementById(`metadata-${qIdx}`);
    if (!metaInput) return;
    const meta = { buckets: getBuckets(qIdx) };
    metaInput.value = JSON.stringify(meta);
}

// ════════════════════════════════════════════════════════════════
// HOTSPOT EDITOR (QT-13 Spot & Find)
// ════════════════════════════════════════════════════════════════
function renderHotspotEditor(qIdx, container, g) {
    container.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="font-size:13px;font-weight:700;color:#334155;">🔍 Hotspot Editor</span>
            <span class="qb-landscape-badge">📱 16:9 landscape</span>
        </div>
        <div class="qb-options-help">Upload a scene image above (🖼️), then click on it to add tap targets. Click a dot to remove.</div>
        <div id="hotspotEditor-${qIdx}"><div class="qb-hotspot-empty">⬆ Pick a scene image above first.</div></div>`;
    hotspotIndex[qIdx] = [];
    const imgInput = document.getElementById(`promptImg-${qIdx}`);
    if (imgInput) {
        imgInput.addEventListener('change', () => renderHotspotImage(qIdx, imgInput.value));
        setTimeout(() => { if (imgInput.value) renderHotspotImage(qIdx, imgInput.value); }, 100);
    }
}

function renderHotspotImage(qIdx, url) {
    const editor = document.getElementById(`hotspotEditor-${qIdx}`);
    if (!editor) return;
    if (!url || !url.trim()) {
        editor.innerHTML = '<div class="qb-hotspot-empty">⬆ Pick a scene image above first.</div>';
        return;
    }
    editor.innerHTML = `
        <div class="qb-hotspot-editor" id="hotspotCanvas-${qIdx}" onclick="addHotspot(event, ${qIdx})">
            <img src="${url}" alt="Scene" onerror="this.parentElement.innerHTML='<div class=\\'qb-hotspot-empty\\'>⚠️ Image failed</div>'">
        </div>
        <div style="margin-top:6px;font-size:12px;color:#64748b;">Hotspots: <span id="hsCount-${qIdx}">0</span> — Click image to add, click dot to remove.</div>`;
    if (hotspotIndex[qIdx]) {
        hotspotIndex[qIdx].forEach((hs, i) => drawHotspot(qIdx, hs.x, hs.y, i+1));
        updateHsCount(qIdx);
    }
}

function addHotspot(e, qIdx) {
    if (e.target.classList.contains('qb-hotspot-dot')) return;
    const canvas = document.getElementById(`hotspotCanvas-${qIdx}`);
    if (!canvas) return;
    const rect = canvas.getBoundingClientRect();
    const x = Math.round(((e.clientX - rect.left) / rect.width) * 1000) / 10;
    const y = Math.round(((e.clientY - rect.top) / rect.height) * 1000) / 10;
    if (!hotspotIndex[qIdx]) hotspotIndex[qIdx] = [];
    hotspotIndex[qIdx].push({ x, y });
    drawHotspot(qIdx, x, y, hotspotIndex[qIdx].length);
    updateHsCount(qIdx);
    saveHotspots(qIdx);
}

function drawHotspot(qIdx, x, y, num) {
    const canvas = document.getElementById(`hotspotCanvas-${qIdx}`);
    if (!canvas) return;
    const dot = document.createElement('div');
    dot.className = 'qb-hotspot-dot';
    dot.style.left = x + '%';
    dot.style.top = y + '%';
    dot.textContent = num;
    dot.onclick = function(e) { e.stopPropagation(); removeHotspot(qIdx, x, y); };
    canvas.appendChild(dot);
}

function removeHotspot(qIdx, x, y) {
    hotspotIndex[qIdx] = hotspotIndex[qIdx].filter(hs => !(Math.abs(hs.x - x) < 0.5 && Math.abs(hs.y - y) < 0.5));
    const canvas = document.getElementById(`hotspotCanvas-${qIdx}`);
    if (canvas) {
        canvas.querySelectorAll('.qb-hotspot-dot').forEach((d, i) => { d.textContent = i + 1; });
    }
    updateHsCount(qIdx);
    saveHotspots(qIdx);
}

function updateHsCount(qIdx) {
    const el = document.getElementById(`hsCount-${qIdx}`);
    if (el) el.textContent = (hotspotIndex[qIdx] || []).length;
}

function saveHotspots(qIdx) {
    const metaInput = document.getElementById(`metadata-${qIdx}`);
    if (!metaInput) return;
    metaInput.value = JSON.stringify({ hotspots: hotspotIndex[qIdx] || [] });
}

// ════════════════════════════════════════════════════════════════
// TRACING NOTICE (QT-12)
// ════════════════════════════════════════════════════════════════
function renderTracingNotice(qIdx, container, g) {
    container.innerHTML = `<div class="no-options-type">✍️ <strong>Tracing</strong> — Upload a dashed/outline PNG above in 🖼️ Image URL. Child traces over it with their finger. <span class="qb-landscape-badge">📱 Full interaction zone</span></div>`;
    const imgInput = document.getElementById(`promptImg-${qIdx}`);
    if (imgInput) {
        const showPreview = () => {
            let preview = document.getElementById(`tracePrev-${qIdx}`);
            if (preview) preview.remove();
            if (imgInput.value) {
                preview = document.createElement('div');
                preview.id = `tracePrev-${qIdx}`;
                preview.className = 'qb-tracing-preview';
                preview.innerHTML = `<img src="${imgInput.value}" onerror="this.style.display='none'"><br><span style="font-size:11px;color:#92400e;">Child sees this faded outline.</span>`;
                container.appendChild(preview);
            }
        };
        imgInput.addEventListener('change', showPreview);
        setTimeout(showPreview, 200);
    }
}

// ════════════════════════════════════════════════════════════════
// GENERIC OPTION EDITOR (QT-01, QT-02, QT-05, QT-06, QT-08, QT-09, QT-10)
// ════════════════════════════════════════════════════════════════
function addOption(qIdx, opts = {}) {
    if (optionIndex[qIdx] === undefined) optionIndex[qIdx] = 0;
    const oIdx = optionIndex[qIdx];
    const container = document.getElementById(`optionsContainer-${qIdx}`);
    const type = selectedTypes[qIdx];
    const code = type ? type.code : '';
    const showCorrect = ['QT-01','QT-02','QT-06','QT-08','QT-09','QT-10'].includes(code);
    const showMatchKey = code === 'QT-05';
    const showMediaToggle = MEDIA_OPTION_TYPES.includes(code);
    const row = document.createElement('div');
    row.className = 'qb-option-row';
    row.id = `option-${qIdx}-${oIdx}`;
    row.innerHTML = `
        <input type="hidden" name="questions[${qIdx}][options][${oIdx}][content_type]" id="opt-ctype-${qIdx}-${oIdx}" value="text">
        ${showCorrect ? `<label style="display:flex;align-items:center;cursor:pointer;"><input type="checkbox" name="questions[${qIdx}][options][${oIdx}][is_correct]" value="1" style="transform:scale(1.2);" ${opts.correct?'checked':''} onchange="this.closest('.qb-option-row').style.borderColor=this.checked?'#22c55e':'#e2e8f0';this.closest('.qb-option-row').style.background=this.checked?'#f0fdf4':'#fff';"><span style="font-size:11px;color:#22c55e;">✅</span></label>` : `<span style="width:22px;text-align:center;color:#cbd5e1;font-size:15px;margin-top:5px;">${oIdx+1}.</span>`}
        <div style="flex:1;display:flex;flex-direction:column;gap:6px;">
            ${(showMediaToggle || showMatchKey) ? `<div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                ${showMediaToggle ? `<div class="qb-ctype-toggle"><button type="button" class="active" onclick="changeContentType(${qIdx},${oIdx},'text',this)">📝</button><button type="button" onclick="changeContentType(${qIdx},${oIdx},'image',this)">🖼️</button></div>` : ''}
                ${showMatchKey ? `<input type="text" name="questions[${qIdx}][options][${oIdx}][match_key]" class="form-control" value="${opts.matchKey||''}" placeholder="Position #" style="width:100px;font-size:11px;">` : ''}
            </div>` : ''}
            <div style="display:flex;gap:8px;align-items:flex-start;">
                <input type="text" id="opt-text-${qIdx}-${oIdx}" name="questions[${qIdx}][options][${oIdx}][text_value]" class="form-control" value="${opts.text||''}" placeholder="Type answer…" style="flex:1;font-size:13px;">
                <div id="opt-image-wrap-${qIdx}-${oIdx}" style="flex:1;display:none;flex-direction:column;gap:4px;">
                    <div style="display:flex;gap:4px;">
                        <input type="text" id="opt-image-${qIdx}-${oIdx}" name="questions[${qIdx}][options][${oIdx}][image_url]" class="form-control" placeholder="Paste URL or browse…" style="flex:1;font-size:13px;" oninput="updateImagePreview(${qIdx},${oIdx},this.value)">
                        <button type="button" class="qb-media-btn" onclick="openMediaPicker('option',${qIdx},'image',${oIdx})">🖼️</button>
                    </div>
                </div>
                <div id="opt-preview-${qIdx}-${oIdx}" style="display:none;"></div>
                <button type="button" onclick="document.getElementById('option-${qIdx}-${oIdx}').remove()" style="background:#94a3b8;color:#fff;border:none;border-radius:4px;padding:5px 8px;cursor:pointer;font-size:12px;">✕</button>
            </div>
        </div>`;
    container.appendChild(row);
    if (opts.correct) { row.style.borderColor = '#22c55e'; row.style.background = '#f0fdf4'; }
    optionIndex[qIdx]++;
}

function changeContentType(qIdx, oIdx, newType, btn) {
    const textInput = document.getElementById(`opt-text-${qIdx}-${oIdx}`);
    const imageWrap = document.getElementById(`opt-image-wrap-${qIdx}-${oIdx}`);
    const preview = document.getElementById(`opt-preview-${qIdx}-${oIdx}`);
    const hiddenType = document.getElementById(`opt-ctype-${qIdx}-${oIdx}`);
    hiddenType.value = newType;
    btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    textInput.style.display = newType === 'text' ? 'block' : 'none';
    if (imageWrap) imageWrap.style.display = newType === 'image' ? 'flex' : 'none';
    preview.style.display = newType === 'image' ? 'block' : 'none';
}

function updateImagePreview(qIdx, oIdx, url) {
    const preview = document.getElementById(`opt-preview-${qIdx}-${oIdx}`);
    if (url && url.trim()) {
        preview.innerHTML = `<img src="${url}" class="qb-img-preview" onerror="this.parentElement.innerHTML='<div class=\\'qb-img-placeholder\\'>⚠️</div>'">`;
    } else {
        preview.innerHTML = '';
    }
}

// ════════════════════════════════════════════════════════════════
// MISC
// ════════════════════════════════════════════════════════════════
function removeQuestion(idx) {
    if (!confirm('Remove this question?')) return;
    document.getElementById(`question-${idx}`).remove();
    delete selectedTypes[idx];
    updateCounter();
    document.querySelectorAll('.qb-question-num .qNum').forEach((el, i) => { el.textContent = i + 1; });
}

function updateCounter() {
    document.getElementById('qCountDisplay').textContent = document.querySelectorAll('.qb-question-card').length;
}

// ── Voice Selection (Create Builder) ──
function selectVoice(idx, voice, el) {
    const pillsContainer = document.getElementById(`voicePills-${idx}`);
    const hiddenInput = document.getElementById(`voiceInput-${idx}`);
    const customDiv = document.getElementById(`customVoice-${idx}`);
    if (!pillsContainer || !hiddenInput) return;
    pillsContainer.querySelectorAll('.qb-voice-pill').forEach(p => p.classList.remove('active'));
    el.classList.add('active');
    if (voice === 'custom') {
        if (customDiv) customDiv.style.display = 'block';
        const ci = customDiv ? customDiv.querySelector('input') : null;
        hiddenInput.value = ci ? ci.value : '';
    } else {
        if (customDiv) customDiv.style.display = 'none';
        hiddenInput.value = voice;
    }
    updateVoicePreviewCreate(idx);
}

function updateVoicePreviewCreate(idx) {
    const promptEl = document.querySelector(`textarea[name="questions[${idx}][prompt]"]`);
    const narrationEl = document.querySelector(`input[name="questions[${idx}][narration_text]"]`);
    const voiceInput = document.getElementById(`voiceInput-${idx}`);
    const preview = document.getElementById(`voicePreview-${idx}`);
    const voiceTag = document.getElementById(`voicePreviewTag-${idx}`);
    const promptPreview = document.getElementById(`voicePreviewPrompt-${idx}`);
    if (!preview) return;
    const prompt = promptEl ? promptEl.value.trim() : '';
    const narration = narrationEl ? narrationEl.value.trim() : '';
    const voice = voiceInput ? voiceInput.value : '';
    const labels = {'leo':'🦁 Leo','lily':'🦋 Lily','max':'🐻 Max','mia':'🐰 Mia','teacher':'👩\u200d🏫 Teacher','custom':'⚙️ Custom'};
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

// ════════════════════════════════════════════════════════════════
// MEDIA PICKER
// ════════════════════════════════════════════════════════════════
let mpContext = null;
let mpSelectedUrl = null;
let mpDebounceTimer = null;

function openMediaPicker(kind, qIdx, mediaType, oIdx) {
    mpContext = { kind, qIdx, mediaType, oIdx: (oIdx === undefined ? null : oIdx) };
    mpSelectedUrl = null;
    document.getElementById('mpTitle').textContent = mediaType === 'audio' ? '🔊 Select Audio' : '🖼️ Select Image';
    document.getElementById('mpSearch').value = '';
    document.getElementById('mpSubject').value = '';
    document.getElementById('mediaPickerOverlay').style.display = 'flex';
    loadMediaItems();
}

function closeMediaPicker() {
    if (mpSelectedUrl && mpContext) applyMediaSelection(mpSelectedUrl);
    document.getElementById('mediaPickerOverlay').style.display = 'none';
    mpContext = null;
    mpSelectedUrl = null;
}

function clearMediaSelection() {
    mpSelectedUrl = null;
    document.querySelectorAll('.mp-item').forEach(el => el.classList.remove('selected'));
}

function debouncedSearch() {
    clearTimeout(mpDebounceTimer);
    mpDebounceTimer = setTimeout(loadMediaItems, 300);
}

function loadMediaItems() {
    const grid = document.getElementById('mpGrid');
    const count = document.getElementById('mpCount');
    grid.innerHTML = '<div class="mp-loading">Loading…</div>';
    const params = new URLSearchParams();
    if (mpContext && mpContext.mediaType) params.set('type', mpContext.mediaType);
    const search = document.getElementById('mpSearch').value.trim();
    if (search) params.set('search', search);
    const subject = document.getElementById('mpSubject').value;
    if (subject) params.set('subject_id', subject);
    fetch(`${window.MEDIA_SEARCH_URL}?${params.toString()}`)
        .then(r => r.json())
        .then(data => {
            if (!data.items || data.items.length === 0) {
                grid.innerHTML = '<div class="mp-empty">No media found.</div>';
                count.textContent = '0 items';
                return;
            }
            count.textContent = `${data.total} item(s)`;
            grid.innerHTML = data.items.map(item => {
                const thumb = item.type === 'image'
                    ? `<img src="${item.thumb_url}" class="mp-thumb" loading="lazy">`
                    : `<div class="mp-thumb">${item.icon || '🔊'}</div>`;
                return `<div class="mp-item" onclick="selectMediaItem(this,'${item.url}')">${thumb}<div class="mp-info"><div class="mp-name">${item.name}</div><div class="mp-meta">${item.width?item.width+'×'+item.height:''}</div></div></div>`;
            }).join('');
        })
        .catch(() => { grid.innerHTML = '<div class="mp-empty">Error loading.</div>'; });
}

function selectMediaItem(el, url) {
    document.querySelectorAll('.mp-item').forEach(i => i.classList.remove('selected'));
    el.classList.add('selected');
    mpSelectedUrl = url;
}

function applyMediaSelection(url) {
    if (!mpContext || !url) return;
    const { kind, qIdx, mediaType } = mpContext;

    if (kind === 'prompt') {
        if (mediaType === 'image') {
            const input = document.getElementById(`promptImg-${qIdx}`);
            const preview = document.getElementById(`promptImgPreview-${qIdx}`);
            if (input) { input.value = url; input.nextElementSibling.classList.add('is-set'); }
            if (preview) { preview.style.display = 'flex'; preview.innerHTML = `<img src="${url}">`; }
            // Trigger hotspot/tracing re-render if applicable
            if (typeof renderHotspotImage === 'function' && document.getElementById(`hotspotEditor-${qIdx}`)) renderHotspotImage(qIdx, url);
        } else {
            const input = document.getElementById(`promptAud-${qIdx}`);
            if (input) { input.value = url; input.nextElementSibling.classList.add('is-set'); }
        }
    } else if (kind === 'option') {
        const oIdx = mpContext.oIdx;
        if (mediaType === 'image') {
            const imgInput = document.getElementById(`opt-image-${qIdx}-${oIdx}`);
            if (imgInput) { imgInput.value = url; updateImagePreview(qIdx, oIdx, url); }
        }
    } else if (kind === 'pair') {
        const side = mpContext.pairSide;
        const pIdx = mpContext.pairIdx;
        const hidden = document.getElementById(`pair-${qIdx}-${pIdx}-${side}-img`);
        const preview = document.getElementById(`pair-${qIdx}-${pIdx}-${side}-preview`);
        if (hidden) hidden.value = url;
        if (preview) {
            preview.style.display = 'block';
            preview.innerHTML = `<img src="${url}" style="max-width:100px; max-height:80px; border-radius:4px; border:1px solid #e2e8f0;">`;
        }
    } else if (kind === 'bucket-item') {
        const iIdx = mpContext.itemIdx;
        const hidden = document.getElementById(`bitem-${qIdx}-${iIdx}-img`);
        const preview = document.getElementById(`bitem-${qIdx}-${iIdx}-preview`);
        if (hidden) hidden.value = url;
        if (preview) {
            preview.style.display = 'block';
            preview.innerHTML = `<img src="${url}" style="max-height:28px; border-radius:4px; border:1px solid #e2e8f0; vertical-align: middle;">`;
        }
    }
}

document.addEventListener('dblclick', function(e) {
    if (e.target.closest('.mp-item') && mpSelectedUrl) closeMediaPicker();
});

document.addEventListener('DOMContentLoaded', function() {
    addQuestion();
});
</script>
@endsection