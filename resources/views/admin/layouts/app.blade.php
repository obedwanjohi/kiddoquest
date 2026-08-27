@php
    $admin = auth()->guard('admin')->user();
    $current = request()->route() ? request()->route()->getName() ?? '' : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — KiddoQuest CBC Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin/app.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <span class="logo">🦁</span>
                <span class="brand-text">KiddoQuest CBC Admin</span>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ url('/admin/dashboard') }}" class="nav-item {{ str_starts_with($current, 'admin.dashboard') ? 'active' : '' }}">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="{{ url('/admin/curricula') }}" class="nav-item {{ str_starts_with($current, 'admin.curricula') ? 'active' : '' }}">
                    <span class="icon">🎓</span> Curricula
                </a>
                <a href="{{ url('/admin/levels') }}" class="nav-item {{ str_starts_with($current, 'admin.levels') ? 'active' : '' }}">
                    <span class="icon">🪜</span> Levels
                </a>
                <a href="{{ url('/admin/curriculum') }}" class="nav-item {{ str_starts_with($current, 'admin.curriculum') ? 'active' : '' }}">
                    <span class="icon">🌳</span> Curriculum Tree
                </a>
                <a href="{{ url('/admin/subjects') }}" class="nav-item {{ str_starts_with($current, 'admin.subjects') ? 'active' : '' }}">
                    <span class="icon">📚</span> Subjects
                </a>
                <a href="{{ url('/admin/topics') }}" class="nav-item {{ str_starts_with($current, 'admin.topics') ? 'active' : '' }}">
                    <span class="icon">📂</span> Sub-Strands
                </a>
                <a href="{{ url('/admin/lessons') }}" class="nav-item {{ str_starts_with($current, 'admin.lessons') ? 'active' : '' }}">
                    <span class="icon">📝</span> Lessons
                </a>
                <a href="{{ url('/admin/missions') }}" class="nav-item {{ str_starts_with($current, 'admin.missions') ? 'active' : '' }}">
                    <span class="icon">🎯</span> Missions
                </a>
                <a href="{{ url('/admin/question-bank') }}" class="nav-item {{ str_starts_with($current, 'admin.question-bank') ? 'active' : '' }}">
                    <span class="icon">🏦</span> Question Banks
                </a>
                <a href="{{ url('/admin/quizzes') }}" class="nav-item {{ str_starts_with($current, 'admin.quizzes') ? 'active' : '' }}">
                    <span class="icon">🎯</span> Quizzes
                </a>
                
                <hr style="margin: 10px 20px; border: none; border-top: 1px solid #e2e8f0;">
                
                <a href="{{ url('/admin/worlds') }}" class="nav-item {{ str_starts_with($current, 'admin.worlds') ? 'active' : '' }}">
                    <span class="icon">🗺️</span> Adventure Worlds
                </a>
                <a href="{{ url('/admin/content-progress') }}" class="nav-item {{ str_starts_with($current, 'admin.content-progress') ? 'active' : '' }}">
                    <span class="icon">📊</span> Content Progress
                </a>

                <hr style="margin: 10px 20px; border: none; border-top: 1px solid #e2e8f0;">

                <a href="{{ url('/admin/media') }}" class="nav-item {{ str_starts_with($current, 'admin.media') ? 'active' : '' }}">
                    <span class="icon">🖼️</span> Media Library
                </a>
                <a href="{{ url('/admin/sounds') }}" class="nav-item {{ str_starts_with($current, 'admin.sounds') ? 'active' : '' }}">
                    <span class="icon">🎵</span> Game Sounds
                </a>
                <a href="{{ url('/admin/voices') }}" class="nav-item {{ str_starts_with($current, 'admin.voices') ? 'active' : '' }}">
                    <span class="icon">🎙️</span> Voices
                </a>
                <a href="{{ url('/admin/users') }}" class="nav-item {{ str_starts_with($current, 'admin.users') ? 'active' : '' }}">
                    <span class="icon">👥</span> Admin Users
                </a>
                <a href="{{ url('/admin/settings') }}" class="nav-item {{ str_starts_with($current, 'admin.settings') ? 'active' : '' }}">
                    <span class="icon">⚙️</span> Settings
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <div class="admin-name">{{ $admin->name ?? 'Admin' }}</div>
                    <div class="admin-role">{{ ucfirst($admin->role ?? 'admin') }}</div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="main-content">
            {{-- Top bar --}}
            <header class="topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <h2>@yield('title', 'Dashboard')</h2>
                </div>
                <div class="topbar-right">
                    <div class="admin-badge">{{ ucfirst($admin->role ?? 'admin') }}</div>
                    <form method="POST" action="{{ url('/admin/logout') }}">
                        @csrf
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                </div>
            </header>

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            {{-- Page content --}}
            <main class="page-content">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>