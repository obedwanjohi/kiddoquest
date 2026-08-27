@extends('admin.layouts.app')

@section('content')
<div class="qb-header">
    <div class="qb-header-content">
        <h1 class="qb-title">📊 Curriculum Progress Dashboard</h1>
        <p class="qb-description">Real-time completion tracking for curriculum production. This data is dynamically calculated.</p>
    </div>
</div>

<div class="dashboard-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Lessons</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold;">{{ $stats['total_lessons'] }}</p>
    </div>
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Missions</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold;">{{ $stats['total_missions'] }}</p>
    </div>
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Questions</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold;">{{ $stats['total_questions'] }} <span style="font-size: 14px; color: #9ca3af;">/ {{ $stats['target_questions'] }}</span></p>
    </div>
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Images</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold;">{{ $stats['images_uploaded'] }} <span style="font-size: 14px; color: #9ca3af;">/ {{ $stats['images_required'] }}</span></p>
    </div>
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Audio</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold;">{{ $stats['audio_uploaded'] }} <span style="font-size: 14px; color: #9ca3af;">/ {{ $stats['audio_required'] }}</span></p>
    </div>
    <div style="background: white; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
        <h3 style="margin: 0; color: #6b7280; font-size: 14px;">Ready Missions</h3>
        <p style="margin: 8px 0 0; font-size: 24px; font-weight: bold; color: #10b981;">{{ $stats['ready_missions'] }}</p>
    </div>
</div>

<div style="background: white; padding: 16px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
    <form method="GET" action="{{ route('admin.content-progress.index') }}" style="display: flex; gap: 16px; align-items: center;">
        <input type="text" name="search" placeholder="Search mission, lesson, bank..." value="{{ request('search') }}" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; flex: 1;">
        <select name="status" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
            <option value="">All Statuses</option>
            <option value="ready" {{ request('status') == 'ready' ? 'selected' : '' }}>🟢 Ready</option>
            <option value="progress" {{ request('status') == 'progress' ? 'selected' : '' }}>🟡 In Progress</option>
            <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>🔴 Incomplete</option>
        </select>
        <button type="submit" class="qb-btn qb-btn-primary">Filter</button>
        @if(request('search') || request('status'))
            <a href="{{ route('admin.content-progress.index') }}" style="color: #6b7280; text-decoration: none;">Clear</a>
        @endif
    </form>
</div>

@foreach($grouped as $worldName => $lessons)
    <h2 style="margin: 32px 0 16px; font-size: 20px; color: #111827; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px;">🌍 {{ $worldName }}</h2>
    
    @foreach($lessons as $lessonName => $missions)
        <h3 style="margin: 16px 0; font-size: 16px; color: #4b5563;">📚 {{ $lessonName }}</h3>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden;">
            <thead style="background: #f9fafb; text-align: left; border-bottom: 1px solid #e5e7eb;">
                <tr>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Mission</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Video</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Question Bank</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500; min-width: 150px;">Questions Progress</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Images</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Audio</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500;">Status</th>
                    <th style="padding: 12px 16px; color: #6b7280; font-weight: 500; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($missions as $m)
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: bold; color: #111827;">{{ $m['title'] }}</div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">Target: {{ $m['questions_target'] }} Qs</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            {!! $m['video_uploaded'] ? '🟢 <span style="font-size:12px;color:#6b7280;">Uploaded</span>' : '🔴 <span style="font-size:12px;color:#ef4444;">Missing</span>' !!}
                        </td>
                        <td style="padding: 12px 16px; font-size: 14px; color: #374151;">
                            {{ $m['bank_name'] }}
                        </td>
                        <td style="padding: 12px 16px;">
                            @php
                                $percent = $m['questions_target'] > 0 ? min(100, round(($m['questions_current'] / $m['questions_target']) * 100)) : 0;
                                $barColor = $percent >= 100 ? '#10b981' : '#fbbf24';
                            @endphp
                            <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; color: #6b7280;">
                                <span>{{ $m['questions_current'] }} / {{ $m['questions_target'] }}</span>
                                <span>{{ $percent }}%</span>
                            </div>
                            <div style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; background: {{ $barColor }}; width: {{ $percent }}%;"></div>
                            </div>
                        </td>
                        <td style="padding: 12px 16px;">
                            @if($m['images_required'] > 0)
                                <button onclick="showMissing('Images', {{ json_encode($m['missing_images']) }})" style="background:none; border:none; cursor:pointer; font-weight:bold; color: {{ $m['images_uploaded'] >= $m['images_required'] ? '#10b981' : '#ef4444' }}; text-decoration: underline;">
                                    {{ $m['images_uploaded'] }} / {{ $m['images_required'] }}
                                </button>
                            @else
                                <span style="color: #9ca3af; font-size: 12px;">N/A</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px;">
                            @if($m['audio_required'] > 0)
                                <span style="font-weight:bold; color: {{ $m['audio_uploaded'] >= $m['audio_required'] ? '#10b981' : '#ef4444' }};">
                                    {{ $m['audio_uploaded'] }} / {{ $m['audio_required'] }}
                                </span>
                            @else
                                <span style="color: #9ca3af; font-size: 12px;">N/A</span>
                            @endif
                        </td>
                        <td style="padding: 12px 16px; font-weight: bold;">
                            {{ $m['status'] }}
                        </td>
                        <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                            @if($m['lesson_id'])
                                <a href="{{ route('admin.lessons.missions.edit', [$m['lesson_id'], $m['id']]) }}" class="qb-btn qb-btn-outline" style="padding: 4px 8px; font-size: 12px;" title="Edit Mission">🎯</a>
                            @endif
                            @if($m['bank_id'])
                                <a href="{{ route('admin.question-banks.questions', $m['bank_id']) }}" class="qb-btn qb-btn-outline" style="padding: 4px 8px; font-size: 12px;" title="Edit Question Bank">❓</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
@endforeach

<dialog id="missingModal" style="border: none; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 0; width: 400px; max-width: 90vw;">
    <div style="padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; border-radius: 8px 8px 0 0;">
        <h3 id="modalTitle" style="margin: 0; font-size: 16px;">Missing Assets</h3>
        <button onclick="document.getElementById('missingModal').close()" style="background: none; border: none; cursor: pointer; font-size: 18px; color: #6b7280;">✕</button>
    </div>
    <div style="padding: 16px; max-height: 300px; overflow-y: auto;">
        <ul id="modalList" style="margin: 0; padding-left: 20px; color: #ef4444; font-size: 14px;"></ul>
    </div>
</dialog>

<script>
function showMissing(type, items) {
    document.getElementById('modalTitle').innerText = 'Missing ' + type;
    const list = document.getElementById('modalList');
    list.innerHTML = '';
    
    if (items.length === 0) {
        list.innerHTML = '<li style="color: #10b981;">All required assets are present!</li>';
    } else {
        items.forEach(item => {
            const li = document.createElement('li');
            li.innerText = item;
            li.style.marginBottom = '8px';
            list.appendChild(li);
        });
    }
    
    document.getElementById('missingModal').showModal();
}
</script>

@endsection
