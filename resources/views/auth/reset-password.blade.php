@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
<div class="text-center mb-4">
    <h1 class="h4 text-white fw-bold">Set new password</h1>
</div>
<div class="card login-card">
    <div class="card-body p-4">
        @if($errors->any())
            <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required class="form-control bg-light border-0">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password">New password</label>
                <input id="password" type="password" name="password" required class="form-control bg-light border-0">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold" for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control bg-light border-0">
            </div>
            <button class="btn btn-primary w-100">Reset Password</button>
        </form>
    </div>
</div>
@endsection
