@extends('layouts.app')
@section('title', 'Biometric Devices')
@section('content')
<div class="alert alert-info small">ZKTeco / device sync API: <code>POST /api/biometric/punches</code> with header <code>X-Device-Token: {token}</code></div>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card card-panel">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.biometrics.store') }}">@csrf
                    <input name="name" class="form-control mb-2" placeholder="Device name" required>
                    <input name="serial" class="form-control mb-2" placeholder="Serial" required>
                    <input name="ip" class="form-control mb-2" placeholder="IP">
                    <input name="location" class="form-control mb-2" placeholder="Location">
                    <select name="branch_id" class="form-select mb-2"><option value="">Branch</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
                    <button class="btn btn-primary btn-sm w-100">Register Device</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Serial</th><th>Token</th><th>Last Sync</th></tr></thead>
                    <tbody>
                    @foreach($devices as $d)
                        <tr>
                            <td>{{ $d->name }}</td>
                            <td>{{ $d->serial }}</td>
                            <td><code class="small">{{ $d->api_token }}</code></td>
                            <td>{{ $d->last_sync_at?->diffForHumans() ?? 'Never' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
