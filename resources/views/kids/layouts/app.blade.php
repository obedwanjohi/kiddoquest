@extends('layouts.app')

@php
    // Theme attribute: applies CSS world theme (forest, safari, ocean, etc.)
    $themeClass = isset($kidTheme) ? 'kid-theme-' . $kidTheme : '';
@endphp

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kid/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kid/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/kid/landscape.css') }}">
    @stack('kid-styles')
@endpush

@section('content')
    <div id="kid-app-viewport" class="kid-app {{ $themeClass }}" style="font-family: var(--kid-font-body); background: var(--kid-bg); color: var(--kid-text);">
        @yield('kid-content')
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/kid/kid-spa-router.js') }}?v={{ time() }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
