@extends('layouts.app')
@section('title', 'Welcome — BZabc Kids')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4"
     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">

    <div class="max-w-lg w-full text-center">

        {{-- Stars decoration --}}
        <div class="text-yellow-300 text-2xl mb-4 animate-pulse">
            ⭐ ✨ ⭐
        </div>

        {{-- Leo the Lion greeting --}}
        <div class="bg-white rounded-3xl shadow-2xl p-8 mb-6 transform hover:scale-105 transition duration-500">
            <div class="text-8xl mb-4 animate-bounce" style="animation-duration: 2s;">🦁</div>

            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                Hi, I'm Leo!
            </h1>

            <p class="text-xl text-purple-600 font-medium mb-4">
                Welcome, {{ $child->name }}! 🎉
            </p>

            <p class="text-gray-600 leading-relaxed mb-6">
                I'm so happy to meet you! We're going to have amazing adventures together.
                We'll explore magical worlds, make new friends, and learn so many cool things!
            </p>

            {{-- Profile summary card --}}
            <div class="bg-purple-50 rounded-2xl p-4 mb-6 text-left">
                <div class="flex items-center gap-4">
                    <div class="text-4xl">{{ $child->avatar_emoji }}</div>
                    <div class="flex-1">
                        <p class="font-bold text-gray-800 text-lg">{{ $child->name }}</p>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 mt-1">
                            <span>🧸 Level: <strong>{{ $child->level_display }}</strong></span>
                            @if($child->age)
                                <span>📅 Age: <strong>{{ $child->age }}</strong></span>
                            @endif
                            @if($child->favorite_color)
                                <span>🎨 Favorite: <strong class="capitalize">{{ $child->favorite_color }}</strong></span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- CTAs --}}
            <div class="space-y-3">
                <a href="{{ route('kids.profiles') }}"
                   class="block w-full bg-gradient-to-r from-purple-600 to-pink-500 text-white py-4 rounded-xl font-bold text-xl hover:shadow-lg hover:scale-105 transition">
                    🚀 Let's Go on an Adventure!
                </a>
                <a href="{{ route('guardian.dashboard') }}"
                   class="block text-gray-500 hover:text-gray-700 text-sm font-medium py-2">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Fun teaser --}}
        <p class="text-white text-sm opacity-90">
            🌳 Explore Whispering Forest &nbsp;•&nbsp; 🦁 Meet Safari friends &nbsp;•&nbsp; 🏰 Discover the Castle
        </p>
    </div>
</div>
@endsection