@extends('admin.layouts.app')
@section('title', 'Question Banks')

@section('content')
<div class="page-header">
    <div>
        <h1>🏦 Question Banks</h1>
        <p class="text-muted">Reusable question pools organized by lesson, subject and quiz type.</p>
    </div>
    <div style="display:flex; gap:10px;">
        <button type="button" class="btn-secondary" onclick="document.getElementById('importCsvModal').style.display='flex'">📥 Import CSV</button>
        <a href="{{ route('admin.question-banks.create') }}" class="btn-primary">+ New Question Bank</a>
    </div>
</div>

{{-- Search + Filters --}}
<form method="GET" class="filter-bar" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Search name or description…" class="form-input" style="flex:1; min-width:200px;">

    <select name="subject" onchange="this.form.submit()">
        <option value="">All Subjects</option>
        @foreach ($subjects as $s)
        <option value="{{ $s->id }}" {{ request('subject') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>

    <select name="quiz_type" onchange="this.form.submit()">
        <option value="">All Types</option>
        @foreach ($quizTypes as $t)
        <option value="{{ $t->id }}" {{ request('quiz_type') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
        @endforeach
    </select>

    <select name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
    </select>

    <select name="difficulty" onchange="this.form.submit()">
        <option value="">All Levels</option>
        <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
        <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
        <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
    </select>

    <button type="submit" class="btn-sm">Filter</button>
    <a href="{{ route('admin.question-banks.index') }}" class="btn-sm">Clear</a>
</form>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
<div class="alert alert-danger" style="background:#fee2e2; color:#991b1b; padding:12px; border-radius:8px; margin-bottom:16px;">
    {{ session('error') }}
</div>
@endif

@if ($banks->isEmpty())
<div class="empty-state">
    <p>No question banks found matching your criteria.</p>
    <div style="display:flex; gap:10px; justify-content:center; margin-top:12px;">
        <button type="button" class="btn-secondary" onclick="document.getElementById('importCsvModal').style.display='flex'">📥 Import CSV</button>
        <a href="{{ route('admin.question-banks.create') }}" class="btn-primary">Create Question Bank</a>
    </div>
</div>
@else
<div class="table-responsive">
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Subject</th>
            <th>Default Type</th>
            <th>Difficulty</th>
            <th>Questions</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($banks as $bank)
        <tr>
            <td>
                <strong><a href="{{ route('admin.question-banks.show', $bank) }}">{{ $bank->name }}</a></strong>
                @if ($bank->description)
                <br><small class="text-muted">{{ Str::limit($bank->description, 60) }}</small>
                @endif
            </td>
            <td>{{ $bank->subject?->name ?? '—' }}</td>
            <td>{{ $bank->quizType?->name ?? '—' }}</td>
            <td>
                @if ($bank->difficulty)
                <span class="badge difficulty-{{ $bank->difficulty }}">{{ ucfirst($bank->difficulty) }}</span>
                @endif
            </td>
            <td>
                <strong>{{ $bank->pool_size }}</strong> /
                @php
                    $totalCount = max($bank->assigned_questions_count, $bank->questions_count);
                @endphp
                {{ $totalCount }}
            </td>
            <td><span class="status-badge status-{{ $bank->status }}">{{ ucfirst($bank->status) }}</span></td>
            <td>
                <a href="{{ route('admin.question-banks.questions', $bank) }}" class="btn-sm btn-primary" title="Manage Questions">📋</a>
                <a href="{{ route('admin.question-banks.show', $bank) }}" class="btn-sm">View</a>
                <a href="{{ route('admin.question-banks.edit', $bank) }}" class="btn-sm">Edit</a>
                <a href="{{ route('admin.question-banks.preview', $bank) }}" class="btn-sm">Preview</a>
                <form method="POST" action="{{ route('admin.question-banks.duplicate', $bank) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="btn-sm">Duplicate</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>

{{ $banks->links() }}
@endif

{{-- 📥 CSV IMPORT MODAL --}}
<div id="importCsvModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
    <div style="background:white; border-radius:16px; width:95%; max-width:600px; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); relative">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid #e2e8f0; padding-bottom:12px;">
            <h3 style="margin:0; font-size:20px; font-weight:800; color:#1e293b;">📥 Import Question Bank via CSV</h3>
            <button type="button" onclick="document.getElementById('importCsvModal').style.display='none'" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b;">✕</button>
        </div>

        <form action="{{ route('admin.question-banks.import-csv') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div style="display:grid; gap:16px;">
                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#475569;">Destination Question Bank:</label>
                    <select name="question_bank_id" class="form-input" style="width:100%; font-weight:700;" onchange="
                        if(this.value) {
                            document.getElementById('newBankNameDiv').style.display='none';
                        } else {
                            document.getElementById('newBankNameDiv').style.display='block';
                        }
                    ">
                        <option value="">➕ Create New Question Bank</option>
                        @if(isset($allQuestionBanks) && $allQuestionBanks->count())
                            <optgroup label="📥 Append to Existing Bank:">
                                @foreach($allQuestionBanks as $qb)
                                    <option value="{{ $qb->id }}">📥 Append to: {{ $qb->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div id="newBankNameDiv">
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#475569;">New Bank Title (Optional):</label>
                    <input type="text" name="name" placeholder="e.g. Pre-Primary Math Safari Bank" class="form-input" style="width:100%;">
                    <small style="color:#64748b; font-size:11px;">Leave empty to use the CSV filename.</small>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div>
                        <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#475569;">Target CBC Subject:</label>
                        <select name="subject_id" class="form-input" style="width:100%;">
                            <option value="">-- Select Subject --</option>
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#475569;">Difficulty & Status:</label>
                        <div style="display:flex; gap:8px;">
                            <select name="difficulty" class="form-input" style="flex:1;">
                                <option value="medium">Medium</option>
                                <option value="easy">Easy</option>
                                <option value="hard">Hard</option>
                            </select>
                            <select name="status" class="form-input" style="flex:1;">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label style="display:block; font-weight:700; font-size:13px; margin-bottom:6px; color:#475569;">Upload CSV File:</label>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required class="form-input" style="width:100%; padding:10px; border:2px dashed #cbd5e1; background:#f8fafc; border-radius:8px;">
                </div>

                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:14px;">
                    <div style="font-weight:700; font-size:13px; color:#1e40af; margin-bottom:4px;">📥 Download Clean CSV Template Per Question Type:</div>
                    <div style="font-size:11px; color:#3b82f6; margin-bottom:10px;">Select a question type to download its dedicated 8-10 column clean Excel template:</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'multiple_choice']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">1. Multiple Choice</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'count_objects']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">2. Count Objects</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'matching']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">3. Matching</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'complete_pattern']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">4. Complete Pattern</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'fill_blank']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">5. Fill Blank</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'true_false']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">6. True / False</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'drag_sequence']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">7. Drag Sequence</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'drag_sort']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">8. Category Sort</a>
                        <a href="{{ route('admin.question-banks.download-sample-csv', ['type' => 'speak_repeat']) }}" class="btn-sm" style="background:#ffffff; border:1px solid #93c5fd; color:#1e40af; font-weight:700; font-size:11px;">9. Listen & Speak</a>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px; border-top:1px solid #e2e8f0; padding-top:16px;">
                <button type="button" class="btn-secondary" onclick="document.getElementById('importCsvModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn-primary" style="font-weight:800;">🚀 Import Question Bank</button>
            </div>
        </form>
    </div>
</div>
@endsection