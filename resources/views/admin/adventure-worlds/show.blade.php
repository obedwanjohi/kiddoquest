@extends('admin.layouts.app')
@section('title', $adventureWorld->name)

@section('content')

<style>
.modal-overlay {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-overlay.active {
    display: flex;
}
.modal-content {
    background: white;
    padding: 24px;
    border-radius: 8px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 32px;">{{ $adventureWorld->icon }}</span>
            <div>
                <h3 style="margin: 0;">{{ $adventureWorld->name }}</h3>
                <div style="font-size: 13px; color: #64748b;">{{ $adventureWorld->description }}</div>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.adventure-worlds.edit', $adventureWorld) }}" class="btn btn-secondary">Edit World</a>
            <a href="{{ route('admin.adventure-worlds.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>🎯 Assigned Missions</h3>
        <div style="display: flex; gap: 8px;">
            <button onclick="document.getElementById('addExistingModal').classList.add('active')" class="btn btn-secondary">➕ Add Existing Mission</button>
            <button onclick="document.getElementById('createModal').classList.add('active')" class="btn btn-primary">➕ Create New Mission</button>
        </div>
    </div>

    <div class="card-body p-0">
        @if($adventureWorld->missions->isEmpty())
            <div style="padding: 30px; text-align: center; color: #64748b;">
                <p>No missions assigned to this world yet.</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Internal Title</th>
                        <th>Kid Display Title</th>
                        <th>Lesson</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adventureWorld->missions as $mission)
                        <tr>
                            <td>#{{ $mission->id }}</td>
                            <td>
                                <strong>{{ $mission->title }}</strong>
                                <div style="font-size: 12px; color: #64748b;">{{ $mission->status }}</div>
                            </td>
                            <td>{{ $mission->display_title }}</td>
                            <td>{{ $mission->lesson->title ?? '—' }}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('admin.lessons.missions.edit', [$mission->lesson_id, $mission]) }}" class="btn btn-secondary btn-sm">Edit Mission</a>
                                    <form action="{{ route('admin.adventure-worlds.missions.remove', [$adventureWorld, $mission]) }}" method="POST" onsubmit="return confirm('Remove this mission from the world?');">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

{{-- Add Existing Mission Modal --}}
<div class="modal-overlay" id="addExistingModal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h4 style="margin: 0;">Add Existing Missions</h4>
            <button onclick="document.getElementById('addExistingModal').classList.remove('active')" style="background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
        </div>
        <form action="{{ route('admin.adventure-worlds.missions.assign', $adventureWorld) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Select Missions</label>
                <select name="mission_ids[]" class="form-control" multiple style="height: 200px;" required>
                    @foreach($availableMissions as $avMission)
                        <option value="{{ $avMission->id }}">
                            {{ $avMission->title }} (Lesson: {{ $avMission->lesson->title ?? 'None' }})
                        </option>
                    @endforeach
                </select>
                <small style="color: #64748b; display: block; margin-top: 4px;">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('addExistingModal').classList.remove('active')" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Selected</button>
            </div>
        </form>
    </div>
</div>

{{-- Create New Mission Modal (Select Lesson) --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h4 style="margin: 0;">Create New Mission</h4>
            <button onclick="document.getElementById('createModal').classList.remove('active')" style="background: none; border: none; font-size: 20px; cursor: pointer;">×</button>
        </div>
        <p style="font-size: 14px; color: #64748b; margin-bottom: 16px;">
            Every mission must belong to a curriculum Lesson. Please select the Lesson this new mission will belong to.
        </p>
        <form action="#" method="GET" id="createMissionForm">
            <input type="hidden" name="adventure_world_id" value="{{ $adventureWorld->id }}">
            <div class="form-group">
                <label class="form-label">Select Curriculum Lesson</label>
                <select id="lessonSelect" class="form-control" required>
                    <option value="">— Select a Lesson —</option>
                    @foreach($lessons as $l)
                        <option value="{{ $l->slug }}">{{ $l->title }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('createModal').classList.remove('active')" class="btn btn-secondary">Cancel</button>
                <button type="button" onclick="submitCreateForm()" class="btn btn-primary">Continue to Mission Form</button>
            </div>
        </form>
    </div>
</div>

<script>
function submitCreateForm() {
    const lessonId = document.getElementById('lessonSelect').value;
    if (!lessonId) {
        alert('Please select a lesson first.');
        return;
    }
    // Redirect to the nested create route with adventure_world_id in query string
    window.location.href = `/admin/lessons/${lessonId}/missions/create?adventure_world_id={{ $adventureWorld->id }}`;
}
</script>

@endsection
