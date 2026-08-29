@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
<a href="{{ route('login') }}" class="small text-decoration-none d-inline-block mb-3">&larr; Back to sign in</a>
<h1 class="h4 fw-bold mb-1">Reset password</h1>
<p class="text-muted small mb-4">Enter your work email. If an account exists, a reset link will be sent.</p>
@if (session('status'))
    <div class="alert alert-success small">{{ session('status') }}</div>
@endif
<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="email">Work email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-control">
    </div>
    <button class="btn btn-primary w-100 py-2">Send reset link</button>
</form>
@endsection
