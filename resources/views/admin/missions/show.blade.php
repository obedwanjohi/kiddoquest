@extends('admin.layouts.app')
@section('title', $mission->title)
@section('content')

@php
    $voiceLabels = ['leo' => '🦁 Leo', 'lily' => '🦋 Lily', 'max' => '🐻 Max', 'mia' => '🐰 Mia', 'teacher' => '👩‍🏫 Teacher', 'custom' => '⚙️ Custom'];
@endphp

<div class="card">
    <div class="card-header">
        <h3>
            @if ($mission->thumbnailMedia)
                <img src="{{ $mission->thumbnailMedia->url }}" style="width:48px;height:48px;border-radius:8px;object-fit:cover;vertical-align:middle;margin-right:8px;">
            @else
                <span style="font-size:32px;">🎯</span>
            @endif
            {{ $mission->title }}
            <span class="badge badge-{{ $mission->status }}">{{ ucfirst(str_replace('_', ' ', $mission->status)) }}</span>
        </h3>
        <div style="display:flex;gap:6px;">
            <a href="{{ route('admin.lessons.missions.index', $lesson) }}" class="btn btn-secondary" style="font-size:12px;">← List</a>
            <a href="{{ route('admin.lessons.missions.edit', [$lesson, $mission]) }}" class="btn btn-secondary" style="font-size:12px;">✏ Edit</a>
            <form action="{{ route('admin.lessons.missions.duplicate', [$lesson, $mission]) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-secondary" style="font-size:12px;">📋 Duplicate</button>
            </form>
        </div>
    </div>
    <div class="card-body">
        {{-- Summary --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h5 style="color:#4f46e5;margin-bottom:8px;">📋 Details</h5>
                <table class="table" style="font-size:13px;">
                    <tr><td style="width:40%;"><strong>Lesson:</strong></td><td>{{ $lesson->title }}</td></tr>
                    <tr><td><strong>Description:</strong></td><td>{{ $mission->description ?: '—' }}</td></tr>
                    <tr><td><strong>Status:</strong></td><td><span class="badge badge-{{ $mission->status }}">{{ ucfirst(str_replace('_', ' ', $mission->status)) }}</span></td></tr>
                    <tr><td><strong>Sort Order:</strong></td><td>{{ $mission->sort_order }}</td></tr>
                    <tr><td><strong>Estimated:</strong></td><td>{{ $mission->estimated_minutes }} min</td></tr>
                </table>
            </div>
            <div>
                <h5 style="color:#4f46e5;margin-bottom:8px;">🎬 Media</h5>
                <table class="table" style="font-size:13px;">
                    <tr><td style="width:40%;"><strong>Thumbnail:</strong></td><td>{{ $mission->thumbnailMedia ? '✓ Media #' . $mission->thumbnail_media_id : '—' }}</td></tr>
                    <tr><td><strong>Video URL:</strong></td><td>{{ $mission->video_url ? '✓ Set' : '—' }}</td></tr>
                    <tr><td><strong>Video Media:</strong></td><td>{{ $mission->videoMedia ? '✓ Media #' . $mission->video_media_id : '—' }}</td></tr>
                    <tr><td><strong>Allow Replay:</strong></td><td>{{ $mission->allow_replay ? '✅ Yes' : '❌ No' }}</td></tr>
                </table>
            </div>
        </div>

        {{-- Narration --}}
        <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
        <h5 style="color:#4f46e5;margin-bottom:8px;">🎙️ Narration</h5>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:12px;">
                <strong style="color:#7c3aed;">Intro</strong>
                @if ($mission->intro_narration_text)
                    <p style="font-style:italic;margin:6px 0;">"{{ $mission->intro_narration_text }}"</p>
                @else
                    <p style="color:#94a3b8;margin:6px 0;">— no intro narration —</p>
                @endif
                @if ($mission->intro_voice_profile)
                    <span class="badge badge-published">🎙️ {{ $voiceLabels[$mission->intro_voice_profile] ?? ucfirst($mission->intro_voice_profile) }}</span>
                @endif
            </div>
            <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:8px;padding:12px;">
                <strong style="color:#7c3aed;">Outro</strong>
                @if ($mission->outro_narration_text)
                    <p style="font-style:italic;margin:6px 0;">"{{ $mission->outro_narration_text }}"</p>
                @else
                    <p style="color:#94a3b8;margin:6px 0;">— no outro narration —</p>
                @endif
                @if ($mission->outro_voice_profile)
                    <span class="badge badge-published">🎙️ {{ $voiceLabels[$mission->outro_voice_profile] ?? ucfirst($mission->outro_voice_profile) }}</span>
                @endif
            </div>
        </div>

        {{-- Assessment --}}
        <hr style="margin:20px 0;border:none;border-top:2px dashed #e2e8f0;">
        <h5 style="color:#4f46e5;margin-bottom:8px;">📊 Assessment</h5>
        <table class="table" style="font-size:13px;max-width:600px;">
            <tr><td style="width:30%;"><strong>Question Bank:</strong></td><td>{{ $mission->questionBank ? '📋 ' . $mission->questionBank->name : '— none linked —' }}</td></tr>
            @if ($mission->questionBank)
                <tr><td><strong>Bank Questions:</strong></td><td>{{ $mission->questionBank->questions->count() }} total</td></tr>
            @endif
            <tr><td><strong>Questions per Session:</strong></td><td>{{ $mission->questions_per_session }}</td></tr>
            <tr><td><strong>Pass Threshold:</strong></td><td>{{ $mission->pass_threshold_percent }}%</td></tr>
            <tr><td><strong>Stars Reward:</strong></td><td>{{ str_repeat('⭐', $mission->stars_reward) }}</td></tr>
        </table>

        @if ($mission->questionBank && $mission->questionBank->questions->count() > 0)
            <div style="margin-top:12px;">
                <a href="{{ route('admin.question-banks.show', $mission->questionBank) }}" class="btn btn-secondary" style="font-size:12px;">📋 View Question Bank</a>
            </div>
        @endif
    </div>
</div>
@endsection