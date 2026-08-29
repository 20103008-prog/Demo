@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
<h1 class="h4 fw-bold mb-1">Sign in</h1>
<p class="text-muted small mb-4">Use the email address issued by HR.</p>

@if ($errors->any())
    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">Work email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
            autocomplete="username" class="form-control" placeholder="name@company.com">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
            <input id="password" type="password" name="password" required
                autocomplete="current-password" class="form-control">
            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password" aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Keep me signed in</label>
        </div>
        @if (Route::has('password.request'))
            <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
        @endif
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2">Sign in</button>
</form>

@if(config('app.env') !== 'production')
<details class="auth-demo mt-4">
    <summary>Demo accounts (local only)</summary>
    <div class="small text-muted mt-2">
        Employee: arjun.sharma@corp.com / demo1234<br>
        Manager: divya.krishnan@corp.com / demo1234<br>
        Admin: admin@corp.com / admin1234
    </div>
</details>
@endif
@endsection
