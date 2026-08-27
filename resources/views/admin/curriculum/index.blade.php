@extends('admin.layouts.app')
@section('title', 'Curriculum')

@section('content')
<div class="page-header">
    <h1>🎓 CBC Curriculum</h1>
    <p class="text-muted">Browse the curriculum hierarchy: Level → Subject → Strand → Sub-Strand → Lesson</p>
</div>

<div class="curriculum-grid">
    @foreach ($levels as $level)
    <a href="{{ url('/admin/curriculum/level/' . $level->id) }}" class="card level-card" style="border-left: 4px solid {{ $level->color }}">
        <div class="card-body">
            <div class="level-icon" style="background: {{ $level->color }}20; color: {{ $level->color }}">{{ $level->icon }}</div>
            <h3>{{ $level->name }}</h3>
            @if ($level->code)<span class="badge">{{ $level->code }}</span>@endif
            <p class="text-muted small">{{ $level->description }}</p>
            @if ($level->min_age && $level->max_age)
            <span class="tag">Ages {{ $level->min_age }}–{{ $level->max_age }}</span>
            @endif
            <div class="stat">
                <strong>{{ $level->subjects_count }}</strong> Subjects
            </div>
        </div>
    </a>
    @endforeach
</div>
@endsection