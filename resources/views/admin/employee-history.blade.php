@extends('layouts.app')
@section('title', 'Employee History')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h5 mb-1 fw-semibold">{{ $employee->name }} — Attendance History</h2>
        <p class="small text-muted mb-0">Last 7 days through {{ $today->format('D, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.today.not.punched') }}" class="btn btn-outline-secondary btn-sm">Back to Not Punched</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Employee', 'value' => $employee->name, 'icon' => 'bi-person', 'color' => 'primary'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Department', 'value' => $employee->department, 'icon' => 'bi-building', 'color' => 'info'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Role', 'value' => ucfirst($employee->role), 'icon' => 'bi-shield-lock', 'color' => 'secondary'])
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Last 7 Days Attendance</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($attendanceRecords as $record)
                            <tr>
                                <td>{{ $record->date->format('d M') }}</td>
                                <td>{{ $record->check_in ?: '—' }}</td>
                                <td>{{ $record->check_out ?: '—' }}</td>
                                <td>{{ $record->hours ?: '—' }}</td>
                                <td>{!! status_badge($record->status) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No attendance records for the last 7 days.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card card-panel mb-3">
            <div class="card-header bg-white border-0 fw-semibold">Late History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>Check In</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($lateHistory as $record)
                            <tr>
                                <td>{{ $record->date->format('d M') }}</td>
                                <td>{{ $record->check_in ?: '—' }}</td>
                                <td>{!! status_badge($record->status) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No late records in the last 7 days.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Leave History</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>From</th><th>To</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($leaveHistory as $leave)
                            <tr>
                                <td>{{ $leave->from_date->format('d M') }}</td>
                                <td>{{ $leave->to_date->format('d M') }}</td>
                                <td>{{ $leave->days }}</td>
                                <td>{!! status_badge($leave->status) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No leave history in the last 7 days.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
