@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
<div class="card card-panel">
    <div class="card-header bg-white border-0 fw-semibold">System Audit Trail</div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light"><tr><th>When</th><th>Action</th><th>Module</th><th>User</th><th>Details</th><th>Severity</th></tr></thead>
            <tbody>
            @foreach($logs as $log)
                <tr>
                    <td class="small text-nowrap">{{ $log->logged_at?->format('d M Y H:i') }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->module }}</td>
                    <td>{{ $log->user_name }} <span class="text-muted small">({{ $log->role }})</span></td>
                    <td class="small">{{ $log->details }}</td>
                    <td>{!! status_badge($log->severity) !!}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
