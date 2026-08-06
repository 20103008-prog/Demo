@extends('layouts.app')
@section('title', 'Today Not Punched')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h5 mb-1 fw-semibold">Still Not Punched Today</h2>
        <p class="small text-muted mb-0">{{ $today->format('D, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
</div>
<div class="card card-panel">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notPunchedEmployees as $employee)
                        <tr>
                            <td>{{ $employee->employee_code }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department }}</td>
                            <td><a href="{{ route('admin.employees.history', $employee) }}" class="btn btn-sm btn-outline-primary">View history</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Everyone has already punched in today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
