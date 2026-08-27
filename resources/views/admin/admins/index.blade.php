@extends('admin.layouts.app')
@section('title', 'Admin Users')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>👥 Admin Users</h3>
        <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">+ New Admin</a>
    </div>
    <div class="card-body">
        @if ($admins->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">👥</div>
                <h3>No admin users</h3>
                <p>Create admin, editor, or reviewer accounts.</p>
                <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">Create Admin</a>
            </div>
        @else
            <table class="table">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($admins as $adminUser)
                        <tr>
                            <td>{{ $adminUser->name }}</td>
                            <td>{{ $adminUser->email }}</td>
                            <td><span class="badge badge-{{ $adminUser->role === 'admin' ? 'published' : 'review' }}">{{ ucfirst($adminUser->role) }}</span></td>
                            <td>
                                @if ($adminUser->is_active)
                                    <span class="badge badge-published">Active</span>
                                @else
                                    <span class="badge badge-draft">Inactive</span>
                                @endif
                            </td>
                            <td><a href="{{ route('admin.admins.edit', $adminUser) }}" class="btn btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection