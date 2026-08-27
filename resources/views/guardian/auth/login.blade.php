@extends('layouts.app')
@section('title', 'Parent Login — KiddoQuest CBC')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-slate-950 p-4">
    <div class="bg-slate-900 border-2 border-indigo-500/30 rounded-3xl shadow-2xl p-8 max-w-md w-full text-white">
        <div class="text-center mb-6">
            <div class="text-4xl mb-2">🦁</div>
            <h1 class="text-2xl font-black text-white">Welcome to KiddoQuest CBC</h1>
            <p class="text-xs text-indigo-300 font-semibold mt-1">Parent Sign In & Learning Control</p>
        </div>

        <form action="{{ url('/parent/login') }}" method="POST" class="space-y-4">
            @csrf
            @if ($errors->any())
                <div class="bg-rose-500/20 border border-rose-500 text-rose-300 px-4 py-3 rounded-xl text-xs font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       style="background-color: #0F172A !important; color: #FFFFFF !important;"
                       class="w-full px-4 py-3 rounded-xl border border-slate-700 font-bold text-sm focus:border-indigo-500 focus:outline-none"
                       placeholder="parent@example.com">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 mb-1.5">Password</label>
                <input type="password" name="password" required
                       style="background-color: #0F172A !important; color: #FFFFFF !important;"
                       class="w-full px-4 py-3 rounded-xl border border-slate-700 font-bold text-sm focus:border-indigo-500 focus:outline-none"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-300 font-semibold">
                    <input type="checkbox" name="remember" id="remember" class="rounded text-indigo-600 bg-slate-800 border-slate-700">
                    <span>Remember me</span>
                </label>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-3.5 rounded-xl text-sm shadow-lg transition-all cursor-pointer">
                Sign In & Continue →
            </button>
        </form>

        <div class="text-center text-xs text-slate-400 mt-6 pt-4 border-t border-slate-800">
            Don't have an account? <a href="{{ url('/parent/register') }}" class="text-indigo-400 hover:text-white font-bold">Create Account</a>
        </div>
    </div>
</div>
@endsection