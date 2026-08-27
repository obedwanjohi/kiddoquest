@extends('admin.layouts.app')
@section('title', 'Edit Adventure World')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>✏️ Edit Adventure World</h3>
        <a href="{{ route('admin.adventure-worlds.index') }}" class="btn btn-secondary" style="font-size:12px;">← Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.adventure-worlds.update', $adventureWorld) }}" method="POST">
            @method('PUT')
            @include('admin.adventure-worlds.form', ['adventureWorld' => $adventureWorld])
        </form>
    </div>
</div>
@endsection
