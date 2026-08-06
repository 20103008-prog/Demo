@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card card-panel">
            <div class="card-body">
                <h2 class="h6 fw-semibold">Today — {{ now()->format('d M Y') }}</h2>
                <p class="mb-1 small">Check-in: <strong>{{ $today?->check_in ?? '—' }}</strong></p>
                <p class="mb-3 small">Check-out: <strong>{{ $today?->check_out ?? '—' }}</strong></p>
                @if($holidayLabel)
                    <div class="alert alert-info py-2 mb-3">
                        Today is a <strong>{{ $holidayLabel }}</strong>. No absence will be recorded for this non-working day.
                    </div>
                @endif
                <form method="POST" action="{{ route('employee.attendance.punch') }}">
                    @csrf
                    <button class="btn btn-primary w-100">
                        @if(!$today?->check_in) Punch In
                        @elseif(!$today?->check_out) Punch Out
                        @else Already Complete
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Attendance History</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>In</th><th>Out</th><th>Hours</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($records as $r)
                        <tr>
                            <td>{{ $r->date->format('D, d M Y') }}</td>
                            <td>{{ $r->check_in ?: '—' }}</td>
                            <td>{{ $r->check_out ?: '—' }}</td>
                            <td>{{ $r->hours }}</td>
                            <td>{!! status_badge($r->status) !!}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No records yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
