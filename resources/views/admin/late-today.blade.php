@extends('layouts.app')
@section('title', 'Late Today')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h5 mb-1 fw-semibold">Late Today</h2>
        <p class="small text-muted mb-0">{{ $today->format('D, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
</div>
<div class="card card-panel">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Check In</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->user->name }}</td>
                        <td>{{ $record->user->department }}</td>
                        <td>{{ $record->check_in }}</td>
                        <td>{!! status_badge($record->status) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No late punches today.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
