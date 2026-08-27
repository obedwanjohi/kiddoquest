<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KiddoQuest CBC')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>[x-cloak] { display: none !important; }</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-100">
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms
             class="alert alert-success fixed top-16 right-4 z-50 bg-emerald-500 text-white font-bold px-4 py-2.5 rounded-2xl shadow-xl border-2 border-white text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error fixed top-16 right-4 z-50 bg-rose-500 text-white font-bold px-4 py-2 rounded-2xl shadow-lg border-2 border-white">
            {{ session('error') }}
        </div>
    @endif

    @yield('content')

    @stack('scripts')
</body>
</html>