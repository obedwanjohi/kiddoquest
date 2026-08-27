@extends('admin.layouts.app')
@section('title', 'Adventure Worlds')

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h3>🗺️ Adventure Worlds</h3>
        <a href="{{ route('admin.adventure-worlds.create') }}" class="btn btn-primary">➕ New World</a>
    </div>
    
    <div class="card-body p-0">
        @if($worlds->isEmpty())
            <div style="padding: 30px; text-align: center; color: #64748b;">
                <p>No Adventure Worlds created yet.</p>
                <a href="{{ route('admin.adventure-worlds.create') }}" class="btn btn-primary" style="margin-top: 10px;">Create your first world</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Order</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Linked Subject</th>
                        <th>Theme Color</th>
                        <th>Missions</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($worlds as $world)
                        <tr>
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <form action="{{ route('admin.adventure-worlds.move', $world) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" style="border: none; background: none; cursor: pointer; color: #64748b; padding: 0; line-height: 1;">▲</button>
                                    </form>
                                    <form action="{{ route('admin.adventure-worlds.move', $world) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" style="border: none; background: none; cursor: pointer; color: #64748b; padding: 0; line-height: 1;">▼</button>
                                    </form>
                                </div>
                            </td>
                            <td style="font-size: 24px;">{{ $world->icon }}</td>
                            <td>
                                <strong>{{ $world->name }}</strong>
                                @if($world->description)
                                    <div style="font-size: 12px; color: #64748b;">{{ Str::limit($world->description, 50) }}</div>
                                @endif
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 4px 8px; border-radius: 12px; background: #e0f2fe; color: #0369a1; font-weight: bold; font-size: 12px;">
                                    {{ $world->subject ? $world->subject->name : $world->subject_name }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 16px; height: 16px; border-radius: 4px; background-color: {{ $world->theme_color }};"></div>
                                    <span style="font-family: monospace; font-size: 12px;">{{ $world->theme_color }}</span>
                                </div>
                            </td>
                            <td>
                                <span style="background: #f1f5f9; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">{{ $world->missions_count }}</span>
                            </td>
                            <td>
                                @if($world->is_locked)
                                    <span style="color: #dc2626; font-size: 12px; font-weight: 600;">🔒 Locked</span>
                                @else
                                    <span style="color: #16a34a; font-size: 12px; font-weight: 600;">🔓 Unlocked</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <a href="{{ route('admin.adventure-worlds.show', $world) }}" class="btn btn-primary btn-sm">Manage Missions</a>
                                    <a href="{{ route('admin.adventure-worlds.edit', $world) }}" class="btn btn-secondary btn-sm">Edit</a>
                                    
                                    <form action="{{ route('admin.adventure-worlds.destroy', $world) }}" method="POST" onsubmit="return confirm('Delete this adventure world? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
@endsection
