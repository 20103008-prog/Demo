@extends('layouts.guest')
@section('content')
<div class="container py-5" style="max-width:420px">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h1 class="h5 fw-semibold mb-3">Enter login OTP</h1>
            @if(session('success'))<div class="alert alert-success small">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('two-factor.verify') }}">@csrf
                <input name="code" class="form-control mb-3" placeholder="6-digit code" required autofocus>
                <button class="btn btn-primary w-100">Verify</button>
            </form>
        </div>
    </div>
</div>
@endsection
