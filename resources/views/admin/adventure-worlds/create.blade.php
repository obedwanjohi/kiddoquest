@extends('admin.layouts.app')
@section('title', 'New Adventure World')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>➕ New Adventure World</h3>
        <a href="{{ route('admin.adventure-worlds.index') }}" class="btn btn-secondary" style="font-size:12px;">← Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.adventure-worlds.store') }}" method="POST">
            @include('admin.adventure-worlds.form')
        </form>
    </div>
</div>
@endsection
