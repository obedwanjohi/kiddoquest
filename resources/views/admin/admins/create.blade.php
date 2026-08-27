@extends('admin.layouts.app')
@section('title', 'New Admin User')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>👥 Create Admin User</h3>
        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">← Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.admins.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" required value="{{ old('name') }}">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required value="{{ old('email') }}">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="admin" @selected(old('role', 'admin'))>Admin (full access)</option>
                    <option value="editor" @selected(old('role'))>Editor (create/edit content)</option>
                    <option value="reviewer" @selected(old('role'))>Reviewer (review/publish)</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" checked> Active Account
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create Admin</button>
                <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection