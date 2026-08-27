@extends('layouts.app')
@section('title', 'Edit Child — BZabc Kids')

@section('content')
<div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('guardian.dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm">← Back</a>
                <span class="text-2xl">{{ $child->avatar_emoji }}</span>
                <h1 class="text-xl font-bold text-purple-600">Edit Profile</h1>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-6">
            <form action="{{ route('guardian.children.update', $child) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Child's Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $child->name) }}" required maxlength="255"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-lg">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Character Friend</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                        @foreach($avatars as $id => $avatar)
                            <label class="cursor-pointer group">
                                <input type="radio" name="avatar" value="{{ $id }}" id="avatar-{{ $id }}" class="peer sr-only" @if(old('avatar', $child->avatar) === $id) checked @endif>
                                <div class="w-full aspect-square flex flex-col items-center justify-center bg-gray-50 border-2 border-transparent rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-100 transition p-1">
                                    <span class="text-2xl sm:text-3xl">{{ $avatar['emoji'] }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('avatar') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Favorite Color <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($colors as $colorName => $colorHex)
                            <label class="cursor-pointer">
                                <input type="radio" name="favorite_color" value="{{ $colorName }}" id="color-{{ $colorName }}" class="peer sr-only" @if(old('favorite_color', $child->favorite_color) === $colorName) checked @endif>
                                <div class="w-10 h-10 rounded-full border-2 border-gray-200 peer-checked:border-gray-800 peer-checked:scale-110 transition shadow-sm"
                                     style="background-color: {{ $colorHex }};"></div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-8">
                    <label for="birthdate" class="block text-sm font-bold text-gray-700 mb-2">
                        Birthdate <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', $child->birthdate?->format('Y-m-d')) }}"
                        max="{{ date('Y-m-d', strtotime('-1 year')) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    @if($child->recommended_level)
                        <p class="text-sm text-purple-600 mt-2">📊 Current Level: <strong>{{ $child->level_display }}</strong></p>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('guardian.dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Cancel</a>
                    <button type="submit" class="bg-purple-600 text-white px-8 py-3 rounded-xl font-bold text-lg hover:bg-purple-700 transition shadow-sm">
                        💾 Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Danger Zone --}}
        <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
            <h3 class="text-lg font-bold text-red-700 mb-2">⚠️ Danger Zone</h3>
            <p class="text-sm text-red-600 mb-4">Deleting a profile permanently removes all progress and stars for this child. This cannot be undone.</p>
            <form action="{{ route('guardian.children.destroy', $child) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $child->name }}'s profile? All progress will be lost.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-red-700 transition">
                    🗑️ Delete Profile
                </button>
            </form>
        </div>
    </main>
</div>
@endsection