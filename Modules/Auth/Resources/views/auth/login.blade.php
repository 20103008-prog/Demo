@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
<div class="text-center mb-4">
    <div class="d-inline-flex align-items-center justify-content-center rounded-4 mb-3" style="width:56px;height:56px;background:rgba(255,255,255,.12);">
        <i class="bi bi-briefcase text-white fs-3"></i>
    </div>
    <h1 class="h4 text-white fw-bold mb-1">HR Payroll System</h1>
    <p class="text-white-50 small mb-0">Sign in to your account</p>
</div>

<div class="card login-card">
    <div class="card-body p-4">
        <div class="role-pill bg-light rounded-3 p-1 d-flex gap-1 mb-4">
            <button type="button" class="btn btn-sm flex-fill active" data-demo-role="employee"
                data-email="arjun.sharma@corp.com" data-password="demo1234">Employee</button>
            <button type="button" class="btn btn-sm flex-fill" data-demo-role="manager"
                data-email="divya.krishnan@corp.com" data-password="demo1234">Manager</button>
            <button type="button" class="btn btn-sm flex-fill" data-demo-role="admin"
                data-email="admin@corp.com" data-password="admin1234">Admin</button>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email', 'arjun.sharma@corp.com') }}" required autofocus
                    class="form-control bg-light border-0">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password">Password</label>
                <div class="input-group">
                    <input id="password" type="password" name="password" value="demo1234" required
                        class="form-control bg-light border-0">
                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="#password">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small" for="remember">Remember me</label>
                </div>
                @if (Route::has('password.request'))
                    <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold">Sign In</button>
        </form>

        <div class="mt-4 p-3 bg-light rounded-3 border">
            <div class="small fw-semibold text-muted mb-1">Demo Credentials</div>
            <div class="small font-monospace text-secondary">
                Employee: arjun.sharma@corp.com / demo1234<br>
                Manager: divya.krishnan@corp.com / demo1234<br>
                Admin: admin@corp.com / admin1234
            </div>
        </div>
    </div>
</div>
@endsection
