@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
<h1 class="h4 fw-bold mb-1">Set a new password</h1>
<p class="text-muted small mb-4">Choose a password you have not used on this portal before.</p>
@if($errors->any())
    <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
@endif
<form method="POST" action="{{ route('password.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <div class="mb-3">
        <label class="form-label" for="email">Work email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">New password</label>
        <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control">
    </div>
    <button class="btn btn-primary w-100 py-2">Update password</button>
</form>
@endsection
