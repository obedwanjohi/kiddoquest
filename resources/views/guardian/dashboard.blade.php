@extends('layouts.app')
@section('title', 'Parent Dashboard — BZabc Kids')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Top Bar --}}
    <header class="bg-white shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🌈</span>
                <h1 class="text-xl font-bold text-purple-600">BZabc Kids</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">Hi, {{ $guardian->name }}!</span>
                <form action="{{ route('guardian.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-sm text-red-500 hover:text-red-700 font-medium">Logout</button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        {{-- Success Message --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">✕</button>
            </div>
        @endif

        {{-- Children Section --}}
        <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-800">👧👦 My Children</h2>
                <a href="{{ route('guardian.children.create') }}" class="text-sm bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                    ➕ Add Child
                </a>
            </div>

            @if($children->isEmpty())
                <div class="text-center py-8">
                    <div class="text-5xl mb-3">🧒</div>
                    <p class="text-gray-500 mb-4">No children added yet. Let's get started!</p>
                    <a href="{{ route('guardian.children.create') }}" class="inline-block bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700">
                        ➕ Add Your First Child
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($children as $child)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-2xl shrink-0">
                                    {{ $child->avatar_emoji }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-800 truncate">{{ $child->name }}</h3>
                                    <p class="text-xs text-gray-500">
                                        🧸 {{ $child->level_display }}
                                        @if($child->age) &nbsp;•&nbsp; 📅 {{ $child->age_display }} @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Stats Row --}}
                            <div class="grid grid-cols-3 gap-2 mb-3 text-center">
                                <div class="bg-yellow-50 rounded-lg py-2">
                                    <p class="text-lg font-bold text-yellow-600">{{ $child->total_stars }}</p>
                                    <p class="text-xs text-gray-500">⭐ Stars</p>
                                </div>
                                <div class="bg-blue-50 rounded-lg py-2">
                                    <p class="text-lg font-bold text-blue-600">{{ $child->progress_percentage }}%</p>
                                    <p class="text-xs text-gray-500">📊 Done</p>
                                </div>
                                <div class="bg-green-50 rounded-lg py-2">
                                    <p class="text-xs font-medium text-green-600 leading-tight pt-1">
                                        {{ $child->has_played ? '🕐 ' . $child->last_played_display : '✨ New!' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Progress Bar --}}
                            <div class="w-full bg-gray-200 rounded-full h-2 mb-3">
                                <div class="bg-gradient-to-r from-purple-500 to-pink-500 rounded-full h-2 transition-all" style="width: {{ $child->progress_percentage }}%"></div>
                            </div>

                            {{-- Primary CTA --}}
                            <div class="mb-2">
                                <a href="{{ route('kids.enter', $child) }}" class="block w-full text-center bg-gradient-to-r from-purple-600 to-pink-500 text-white py-2.5 rounded-lg hover:shadow-md text-sm font-bold transition">
                                    {{ $child->has_played ? '🚀 Continue Adventure' : '🌟 Let\'s Go!' }}
                                </a>
                            </div>

                            {{-- Secondary Actions --}}
                            <div class="flex gap-2">
                                <a href="{{ route('guardian.children.edit', $child) }}" class="flex-1 text-center text-gray-500 border border-gray-200 py-1.5 rounded-lg hover:bg-gray-50 text-xs font-medium">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('guardian.children.destroy', $child) }}" method="POST" onsubmit="return confirm('Delete {{ $child->name }}'s profile? This cannot be undone.');" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-red-500 border border-red-200 py-1.5 rounded-lg hover:bg-red-50 text-xs font-medium">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Progress Summary (placeholder) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="text-3xl mb-2">📚</div>
                <h3 class="font-bold text-gray-800">Lessons</h3>
                <p class="text-sm text-gray-500">Coming in Milestone 2</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="text-3xl mb-2">🎯</div>
                <h3 class="font-bold text-gray-800">Quizzes</h3>
                <p class="text-sm text-gray-500">Coming in Milestone 4</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="text-3xl mb-2">🏆</div>
                <h3 class="font-bold text-gray-800">Badges</h3>
                <p class="text-sm text-gray-500">Coming in Milestone 5</p>
            </div>
        </div>
    </main>
</div>
@endsection