<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sign in') — HR Payroll</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="login-page d-flex align-items-center justify-content-center p-3">
        <div class="w-100" style="max-width:420px;">
            @yield('content')
            <p class="text-center text-white-50 small mt-4 mb-0">© {{ date('Y') }} HR Payroll Management System</p>
        </div>
    </div>
</body>
</html>
