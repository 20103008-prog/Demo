<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') — {{ config('app.name', 'HR Payroll') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <div class="auth-shell">
        <aside class="auth-brand">
            <div>
                <div class="auth-mark">HR</div>
                <h1 class="auth-brand-title">{{ config('app.name', 'HR Payroll') }}</h1>
                <p class="auth-brand-copy">Staff portal for attendance, leave, shifts, and payroll. Sign in with your office email.</p>
            </div>
            <p class="auth-brand-footer mb-0">© {{ date('Y') }} {{ config('app.name', 'HR Payroll') }}. Internal use only.</p>
        </aside>
        <main class="auth-main">
            <div class="auth-card">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('scripts')
</body>
</html>
