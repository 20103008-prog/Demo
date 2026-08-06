@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
<div class="text-center mb-4">
    <h1 class="h4 text-white fw-bold">Reset password</h1>
    <p class="text-white-50 small">Enter your email for a reset link</p>
</div>
<div class="card login-card">
    <div class="card-body p-4">
        <a href="{{ route('login') }}" class="small text-decoration-none d-inline-block mb-3">&larr; Back to sign in</a>
        @if (session('status'))
            <div class="alert alert-success small">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required class="form-control bg-light border-0">
            </div>
            <button class="btn btn-primary w-100">Send Reset Link</button>
        </form>
    </div>
</div>
@endsection
