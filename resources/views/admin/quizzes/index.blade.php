@extends('admin.layouts.app')
@section('title', 'Quizzes')
@section('content')
<div class="card">
    <div class="card-header">
        <h3>🎯 Quizzes</h3>
        <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">+ New Quiz</a>
    </div>
    <div class="card-body">
        @if ($quizzes->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">🎯</div>
                <h3>No quizzes yet</h3>
                <p>Create a quiz bundle and attach it to a lesson to test what children learned.</p>
                <a href="{{ route('admin.quizzes.create') }}" class="btn btn-primary">Create Quiz</a>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Lesson</th>
                        <th>Subject</th>
                        <th>Questions</th>
                        <th>Pass %</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($quizzes as $quiz)
                        <tr>
                            <td><strong>{{ $quiz->title }}</strong></td>
                            <td>{{ $quiz->lesson->title ?? '—' }}</td>
                            <td>{{ $quiz->lesson->topic->subject->name ?? '—' }}</td>
                            <td>{{ $quiz->questions_count }}</td>
                            <td>{{ $quiz->pass_threshold_percent }}%</td>
                            <td><span class="badge badge-{{ $quiz->status }}">{{ ucfirst(str_replace('_', ' ', $quiz->status)) }}</span></td>
                            <td>
                                <a href="{{ route('admin.quizzes.show', $quiz) }}" class="btn btn-secondary" style="font-size:12px;">Builder</a>
                                <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="btn btn-secondary" style="font-size:12px;">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection