@extends('layouts.guest')
@section('title', 'Verify login')
@section('content')
<h1 class="h5 fw-bold mb-1">Enter login code</h1>
<p class="text-muted small mb-4">A 6-digit code was sent to your email. It expires in 10 minutes.</p>
@if(session('success'))
    <div class="alert alert-success small">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
@endif
<form method="POST" action="{{ route('two-factor.verify') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label" for="code">Verification code</label>
        <input id="code" name="code" class="form-control" inputmode="numeric" autocomplete="one-time-code" placeholder="000000" required autofocus>
    </div>
    <button class="btn btn-primary w-100 py-2">Verify</button>
</form>
@endsection
