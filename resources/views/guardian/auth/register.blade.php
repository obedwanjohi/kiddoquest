@extends('layouts.app')
@section('title', 'Create Free Parent Account — KiddoQuest CBC')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 p-4 relative overflow-hidden py-12">
    {{-- Floating Emojis in background --}}
    <div class="absolute top-10 left-10 text-5xl opacity-20 select-none animate-bounce">🎈</div>
    <div class="absolute bottom-12 right-12 text-6xl opacity-20 select-none animate-pulse">🦁</div>
    <div class="absolute top-20 right-20 text-4xl opacity-20 select-none">⭐</div>

    <div class="bg-white rounded-[2.5rem] shadow-2xl p-8 max-w-md w-full text-slate-800 relative z-10 border-4 border-white/40">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-2xl bg-amber-100 flex items-center justify-center text-4xl mx-auto mb-3 shadow-inner">
                🦁
            </div>
            <h1 class="font-heading text-3xl font-black text-slate-900">Create Parent Account</h1>
            <p class="text-xs text-slate-500 font-semibold mt-1">Start your child's 7-day free learning adventure</p>
        </div>

        <form action="{{ url('/parent/register') }}" method="POST" class="space-y-3.5">
            @csrf
            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-600 px-4 py-3 rounded-2xl text-xs font-bold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Parent Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Jane Mwangi"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="parent@example.com"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">M-Pesa Phone Number <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0712 345 678"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required placeholder="At least 8 characters"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required placeholder="Re-type password"
                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 font-medium text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 outline-none">
            </div>

            <button type="submit"
                    class="w-full font-heading font-black text-base bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white py-4 rounded-2xl shadow-lg hover:shadow-purple-500/30 transition transform hover:scale-102 cursor-pointer mt-2">
                Create Account & Add Kids 🚀
            </button>
        </form>

        <div class="text-center text-xs text-slate-500 mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-slate-500 hover:text-slate-800 font-bold">← Back to Home</a>
            <a href="{{ url('/parent/login') }}" class="text-purple-600 hover:text-purple-800 font-bold">Already have an account?</a>
        </div>
    </div>
</div>
@endsection