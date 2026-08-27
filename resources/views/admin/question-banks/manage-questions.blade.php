@extends('admin.layouts.app')
@section('title', 'Manage Questions — ' . $questionBank->name)

@section('content')
<div class="page-header">
    <a href="{{ route('admin.question-banks.show', $questionBank) }}" class="btn-back">← {{ $questionBank->name }}</a>
    <div>
        <h1>📋 Manage Questions</h1>
        <p class="text-muted">{{ $questionBank->name }}</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

{{-- ── Statistics Cards ── --}}
<div class="stats-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:16px; margin-bottom:24px;">
    <div class="stat-card" style="background:#f0f4ff; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:28px; font-weight:bold; color:#3b82f6;">{{ $assigned->count() }}</div>
        <div class="text-muted">Assigned</div>
    </div>
    <div class="stat-card" style="background:#f0fdf4; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:28px; font-weight:bold; color:#22c55e;">{{ $available->total() }}</div>
        <div class="text-muted">Available (filtered)</div>
    </div>
    <div class="stat-card" style="background:#fef3c7; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:28px; font-weight:bold; color:#f59e0b;">{{ $questionBank->pool_size }}</div>
        <div class="text-muted">Draw Size (pool_size)</div>
    </div>
    <div class="stat-card" style="background:#fdf2f8; padding:16px; border-radius:8px; text-align:center;">
        <div style="font-size:28px; font-weight:bold; color:#ec4899;">
            {{ min($questionBank->pool_size, $assigned->count()) }} / {{ $assigned->count() }}
        </div>
        <div class="text-muted">Random Draw</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">

    {{-- ══════════════════════════════════════ --}}
    {{-- LEFT PANEL: Available Questions       --}}
    {{-- ══════════════════════════════════════ --}}
    <div>
        <h2 style="margin-bottom:12px;">📚 Available Questions</h2>

        {{-- Filters --}}
        <form method="GET" id="filter-form" style="background:#f9fafb; padding:12px; border-radius:8px; margin-bottom:16px;">
            <input type="text" name="search" placeholder="Search prompt…" value="{{ request('search') }}"
                   style="width:100%; margin-bottom:8px; padding:6px 8px; border:1px solid #d1d5db; border-radius:4px;">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                <select name="level" style="padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">All Levels</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(request('level') == $level->id)>{{ $level->name }}</option>
                    @endforeach
                </select>

                <select name="subject" style="padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">All Subjects</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject', $questionBank->subject_id) == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>

                <select name="lesson" style="padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">All Lessons</option>
                    @foreach ($lessons as $lesson)
                        <option value="{{ $lesson->id }}" @selected(request('lesson') == $lesson->id)>{{ $lesson->title }}</option>
                    @endforeach
                </select>

                <select name="quiz_type" style="padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">All Types</option>
                    @foreach ($quizTypes as $type)
                        <option value="{{ $type->id }}" @selected(request('quiz_type') == $type->id)>{{ $type->name }}</option>
                    @endforeach
                </select>

                <select name="difficulty" style="padding:6px; border:1px solid #d1d5db; border-radius:4px;">
                    <option value="">All Difficulty</option>
                    <option value="easy" @selected(request('difficulty') == 'easy')>Easy</option>
                    <option value="medium" @selected(request('difficulty') == 'medium')>Medium</option>
                    <option value="hard" @selected(request('difficulty') == 'hard')>Hard</option>
                </select>

                <button type="submit" class="btn-primary" style="padding:6px 12px;">🔍 Filter</button>
            </div>
        </form>

        {{-- Assign form wraps the table --}}
        <form method="POST" action="{{ route('admin.question-banks.questions.assign', $questionBank) }}">
            @csrf
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <button type="submit" class="btn-primary">➕ Assign Selected</button>
                <button type="submit" name="assign_all_filtered" value="1" class="btn-secondary"
                        onclick="return confirm('Assign ALL {{ $available->total() }} filtered questions?')">
                    ⚡ Assign All Filtered
                </button>
            </div>

            @if ($available->isEmpty())
                <div class="empty-state" style="padding:24px; text-align:center; color:#9ca3af;">
                    No questions match these filters.
                </div>
            @else
                <div style="max-height:600px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:8px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead style="position:sticky; top:0; background:#fff; z-index:1;">
                            <tr style="border-bottom:2px solid #e0e0e0;">
                                <th style="padding:8px; width:36px;"><input type="checkbox" id="select-all"></th>
                                <th style="padding:8px; text-align:left;">Question</th>
                                <th style="padding:8px; text-align:left;">Type</th>
                                <th style="padding:8px; text-align:left;">Diff.</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($available as $q)
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:8px;">
                                    <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="row-checkbox">
                                </td>
                                <td style="padding:8px;">
                                    <div>{{ \Illuminate\Support\Str::limit($q->prompt, 80) }}</div>
                                    @if ($q->quiz && $q->quiz->lesson)
                                        <small class="text-muted">{{ $q->quiz->lesson->title }}</small>
                                    @endif
                                </td>
                                <td style="padding:8px;">
                                    @if ($q->quizType)
                                        <span class="tag small">{{ $q->quizType->name }}</span>
                                    @endif
                                </td>
                                <td style="padding:8px;">
                                    <span class="badge badge-{{ $q->difficulty }}">{{ $q->difficulty }}</span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div style="margin-top:12px;">
                    {{ $available->links() }}
                </div>
            @endif
        </form>
    </div>

    {{-- ══════════════════════════════════════ --}}
    {{-- RIGHT PANEL: Assigned Questions        --}}
    {{-- ══════════════════════════════════════ --}}
    <div>
        <h2 style="margin-bottom:12px;">
            ✅ In This Bank
            <span class="badge badge-info">{{ $assigned->count() }} Questions Assigned</span>
        </h2>

        @if ($assigned->isEmpty())
            <div class="empty-state" style="padding:24px; text-align:center; color:#9ca3af;">
                No questions assigned yet. Use the left panel to add questions.
            </div>
        @else
            <form method="POST" action="{{ route('admin.question-banks.questions.bulk-remove', $questionBank) }}"
                  onsubmit="return confirm('Remove selected questions from this bank?')">
                @csrf
                <div style="margin-bottom:8px;">
                    <button type="submit" class="btn-danger">🗑️ Remove Selected</button>
                </div>

                <div style="max-height:600px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:8px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead style="position:sticky; top:0; background:#fff; z-index:1;">
                            <tr style="border-bottom:2px solid #e0e0e0;">
                                <th style="padding:8px; width:36px;"><input type="checkbox" id="select-all-assigned"></th>
                                <th style="padding:8px; width:36px;">#</th>
                                <th style="padding:8px; text-align:left;">Question</th>
                                <th style="padding:8px; text-align:left;">Type</th>
                                <th style="padding:8px; text-align:left;">Diff.</th>
                                <th style="padding:8px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($assigned as $i => $q)
                            <tr style="border-bottom:1px solid #f0f0f0;">
                                <td style="padding:8px;">
                                    <input type="checkbox" name="question_ids[]" value="{{ $q->id }}" class="row-checkbox-assigned">
                                </td>
                                <td style="padding:8px; color:#9ca3af;">{{ $i + 1 }}</td>
                                <td style="padding:8px;">
                                    <div>{{ \Illuminate\Support\Str::limit($q->prompt, 60) }}</div>
                                    @if ($q->quiz && $q->quiz->lesson)
                                        <small class="text-muted">{{ $q->quiz->lesson->title }}</small>
                                    @endif
                                </td>
                                <td style="padding:8px;">
                                    @if ($q->quizType)
                                        <span class="tag small">{{ $q->quizType->name }}</span>
                                    @endif
                                </td>
                                <td style="padding:8px;">
                                    <span class="badge badge-{{ $q->difficulty }}">{{ $q->difficulty }}</span>
                                </td>
                                <td style="padding:8px;">
                                    <button type="button"
                                            class="btn-sm btn-danger"
                                            onclick="removeSingle({{ $q->id }}, this)"
                                            style="padding:2px 8px; font-size:12px;">✕</button>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
// Select-all checkboxes for both panels
document.getElementById('select-all')?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
});
document.getElementById('select-all-assigned')?.addEventListener('change', function() {
    document.querySelectorAll('.row-checkbox-assigned').forEach(cb => cb.checked = this.checked);
});

// Per-row DELETE submit (avoids nested-form problem)
// Builds a throwaway form outside the bulk-remove form and POSTs it.
function removeSingle(questionId, btn) {
    if (!confirm('Remove this question from the bank?')) return;

    const bankId = {{ $questionBank->id }};
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/question-banks/${bankId}/questions/${questionId}`;
    form.style.display = 'none';

    form.innerHTML = `
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="DELETE">
    `;

    document.body.appendChild(form);
    form.submit();
}
</script>
@endsection