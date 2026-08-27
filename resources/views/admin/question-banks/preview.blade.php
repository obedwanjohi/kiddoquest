@extends('admin.layouts.app')
@section('title', 'Preview Draw — ' . $questionBank->name)

@section('content')
<div class="page-header">
    <a href="{{ route('admin.question-banks.show', $questionBank) }}" class="btn-back">← {{ $questionBank->name }}</a>
    <div>
        <h1>👁 Preview Draw</h1>
        <p class="text-muted">
            Pool of {{ $questionBank->questions->count() }} questions · Drawing <strong>{{ $drawn->count() }}</strong> / requested <strong>{{ $questionBank->pool_size }}</strong>
            · Shuffle: {{ $questionBank->shuffle ? 'ON' : 'OFF' }}
        </p>
    </div>
</div>

<div class="alert alert-info">
    🔄 This is a random draw simulation. <strong>Refresh the page</strong> to see a different selection (when shuffle is ON).
</div>

@if ($drawn->isEmpty())
<div class="empty-state">
    <p>No questions available to draw.</p>
</div>
@else
<div style="margin-bottom:20px; display:flex; gap:8px;">
    <a href="{{ route('admin.question-banks.preview', $questionBank) }}" class="btn-primary">🔄 Draw Again</a>
    <a href="{{ route('admin.question-banks.show', $questionBank) }}" class="btn-sm">← Back to Bank</a>
</div>

<div class="preview-list">
@foreach ($drawn as $i => $q)
    <div class="preview-card" style="border:1px solid #e0e0e0; border-radius:8px; padding:16px; margin-bottom:12px;">
        <div style="display:flex; gap:12px; align-items:start;">
            <span class="badge">{{ $i + 1 }}</span>
            <div style="flex:1;">
                <p><strong>{{ $q->prompt }}</strong></p>
                @if ($q->quizType)
                <span class="tag small">{{ $q->quizType->name }}</span>
                @endif
                @if ($q->options->isNotEmpty())
                <ul style="margin-top:8px; padding-left:20px; list-style:disc;">
                    @foreach ($q->options as $opt)
                    <li style="{{ $opt->is_correct ? 'color:#15803d; font-weight:700;' : 'color:#475569;' }} margin-bottom:4px;">
                        @if($opt->image_url)
                            <img src="{{ $opt->image_url }}" style="height:28px; vertical-align:middle; border-radius:4px; margin-right:4px;">
                        @endif
                        <span>{{ $opt->text_value ?: basename($opt->image_url) }}</span>
                        @if ($opt->match_key)
                            <span style="font-size:11px; background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; margin-left:6px; font-weight:700;">🏷️ {{ $opt->match_key }}</span>
                        @endif
                        @if ($opt->is_correct) ✅ @endif
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
@endforeach
</div>
@endif
@endsection