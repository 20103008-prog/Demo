@extends('layouts.app')
@section('title', 'Profile')
@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Profile information</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')
                    <div class="mb-3">
                        <label class="form-label small">Name</label>
                        <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <button class="btn btn-primary btn-sm">Save</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Update password</div>
            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')
                    <div class="mb-3">
                        <label class="form-label small">Current password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">New password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-primary btn-sm">Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
