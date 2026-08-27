@extends('layouts.app')
@section('title', 'Parent Sign In — KiddoQuest CBC')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 p-4 relative overflow-hidden">
    {{-- Floating Emojis in background --}}
    <div class="absolute top-10 left-10 text-5xl opacity-20 select-none animate-bounce">⭐</div>
    <div class="absolute bottom-12 right-12 text-6xl opacity-20 select-none animate-pulse">🦁</div>
    <div class="absolute top-20 right-20 text-4xl opacity-20 select-none">🎨</div>

    <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 max-w-md w-full text-slate-800 relative z-10 border-4 border-white/40">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 flex items-center justify-center text-4xl mx-auto mb-3 shadow-inner">
                🦁
            </div>
            <h1 class="font-heading text-3xl font-black text-slate-900">Parent Sign In</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Manage child profiles, learning reports & PIN</p>
        </div>

        <form action="{{ url('/parent/login') }}" method="POST" class="space-y-4">
            @csrf
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-2xl text-xs font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Parent Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none"
                       placeholder="parent@example.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-semibold">
                    <input type="checkbox" name="remember" id="remember" class="rounded text-purple-600">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full font-heading font-black text-base bg-gradient-to-r from-purple-600 to-pink-600 text-white py-4 rounded-2xl shadow-lg hover:shadow-purple-500/30 transition transform hover:scale-102 cursor-pointer">
                Sign In & Go to Kids →
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-slate-500 hover:text-slate-800 font-bold">← Back to Home</a>
            <a href="{{ url('/parent/register') }}" class="text-purple-600 hover:text-purple-800 font-bold">Create New Account</a>
        </div>
    </div>
</div>
@endsection