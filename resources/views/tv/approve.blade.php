@extends('layouts.app')

@section('title', 'Sign in a TV — KiddoQuest CBC')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl p-8">
        <h1 class="text-3xl font-bold text-slate-800 mb-2">Sign in a TV</h1>
        <p class="text-slate-600 mb-6">
            Open KiddoQuest on your television. It will show a six-character code —
            type it here and the TV will sign itself in.
        </p>

        @if (session('status'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @error('code')
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('tv.approve.submit') }}" class="space-y-6">
            @csrf

            <input
                type="text"
                name="code"
                maxlength="8"
                autocomplete="off"
                autocapitalize="characters"
                autofocus
                placeholder="ABC123"
                class="w-full text-center tracking-[0.4em] uppercase text-4xl font-extrabold
                       rounded-2xl border-2 border-slate-300 focus:border-indigo-500 focus:ring-0
                       py-5 text-slate-800 placeholder-slate-300"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
            >

            <button
                type="submit"
                class="w-full rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white text-lg
                       font-bold py-4 transition">
                Approve this TV
            </button>
        </form>

        <p class="text-sm text-slate-500 mt-6">
            A code only works once, and only for ten minutes. If it has expired,
            ask the television for a new one.
        </p>
    </div>
</div>
@endsection
