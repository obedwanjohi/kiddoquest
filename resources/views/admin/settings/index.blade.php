@extends('admin.layouts.app')
@section('title', 'Settings')
@section('content')

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    {{-- ── Quiz Type Management ── --}}
    <div class="card">
        <div class="card-header">
            <h3>🎯 Quiz Types</h3>
            <span style="font-size:11px;color:#999;">Enable/disable question types</span>
        </div>
        <div class="card-body">
            @if ($quizTypes->isEmpty())
                <p style="color:#999;text-align:center;padding:16px;">No quiz types seeded.</p>
            @else
                <table class="table">
                    <thead>
                        <tr><th>Name</th><th>Slug</th><th>Order</th><th>Status</th><th>Toggle</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($quizTypes as $type)
                            <tr>
                                <td>
                                    <form action="{{ route('admin.settings.quiz-types.update', $type) }}" method="POST" style="display:flex;gap:4px;">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $type->name }}" class="form-control" style="font-size:13px;width:140px;">
                                    </form>
                                </td>
                                <td style="font-size:12px;color:#999;font-family:monospace;">{{ $type->slug }}</td>
                                <td>
                                    <form action="{{ route('admin.settings.quiz-types.update', $type) }}" method="POST" style="display:flex;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="sort_order" value="{{ $type->sort_order }}" class="form-control" style="font-size:13px;width:50px;">
                                        <input type="hidden" name="name" value="{{ $type->name }}">
                                        <input type="hidden" name="slug" value="{{ $type->slug }}">
                                        <input type="hidden" name="is_active" value="{{ $type->is_active ? 1 : 0 }}">
                                        <button type="submit" class="btn btn-secondary" style="font-size:11px;padding:4px 8px;">✓</button>
                                    </form>
                                </td>
                                <td>
                                    @if ($type->is_active)
                                        <span class="badge badge-published">Active</span>
                                    @else
                                        <span class="badge badge-archived">Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.settings.quiz-types.toggle', $type) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn {{ $type->is_active ? 'btn-secondary' : 'btn-primary' }}" style="font-size:11px;">
                                            {{ $type->is_active ? '🚫 Disable' : '✅ Enable' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── System Info ── --}}
    <div class="card">
        <div class="card-header"><h3>ℹ️ System Information</h3></div>
        <div class="card-body">
            <table class="table" style="font-size:13px;">
                <tr><td style="color:#999;width:50%;">Laravel Version</td><td>{{ app()->version() }}</td></tr>
                <tr><td style="color:#999;">PHP Version</td><td>{{ PHP_VERSION }}</td></tr>
                <tr><td style="color:#999;">Database Driver</td><td>{{ config('database.default') }}</td></tr>
                <tr><td style="color:#999;">Environment</td><td><span class="badge badge-{{ app()->environment('production') ? 'published' : 'draft' }}">{{ app()->environment() }}</span></td></tr>
                <tr><td style="color:#999;">App URL</td><td style="font-size:12px;font-family:monospace;">{{ config('app.url') }}</td></tr>
                <tr><td style="color:#999;">Timezone</td><td>{{ config('app.timezone') }}</td></tr>
                <tr><td style="color:#999;">Debug Mode</td><td>{{ config('app.debug') ? '✅ ON' : '❌ OFF' }}</td></tr>
            </table>
        </div>
    </div>

</div>

{{-- ── Admin Profile / Account ── --}}
<div class="card" style="margin-top:16px;">
    <div class="card-header"><h3>👤 Your Account</h3></div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:16px;">
            <div style="width:64px;height:64px;border-radius:50%;background:#4f46e5;color:white;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:bold;">
                {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
            </div>
            <div>
                <div style="font-size:18px;font-weight:bold;color:#1e293b;">{{ auth('admin')->user()->name }}</div>
                <div style="font-size:13px;color:#999;">{{ auth('admin')->user()->email }}</div>
                <div style="margin-top:4px;"><span class="badge badge-published">{{ ucfirst(auth('admin')->user()->role) }}</span></div>
            </div>
        </div>
    </div>
</div>

@endsection