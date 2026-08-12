@extends('layouts.app')
@section('title', 'Two-Factor Auth')
@section('content')
<div class="card card-panel" style="max-width:480px">
    <div class="card-body">
        <p class="small text-muted">Email OTP at login when enabled. (Mail driver: log / SMTP)</p>
        <form method="POST" action="{{ route('employee.twofactor.toggle') }}">@csrf
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" name="enabled" value="1" id="tfa" @checked($user->two_factor_enabled)>
                <label class="form-check-label" for="tfa">Enable 2FA</label>
            </div>
            <button class="btn btn-primary btn-sm">Save</button>
        </form>
    </div>
</div>
@endsection
