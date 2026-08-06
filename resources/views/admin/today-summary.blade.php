@extends('layouts.app')
@section('title', 'Today Attendance Summary')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="h5 mb-1 fw-semibold">Today's Attendance Summary</h2>
        <p class="small text-muted mb-0">{{ $today->format('D, d M Y') }}</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to Dashboard</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Present Today', 'value' => $presentToday, 'icon' => 'bi-check2-circle', 'color' => 'success'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Late Today', 'value' => $lateToday, 'icon' => 'bi-clock-history', 'color' => 'danger'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'On Leave Today', 'value' => $onLeaveToday, 'icon' => 'bi-calendar-minus', 'color' => 'warning'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Punches Today', 'value' => $punchesToday, 'icon' => 'bi-eye', 'color' => 'primary'])
    </div>
    <div class="col-md-4">
        @include('components.kpi', ['title' => 'Still Not Punched', 'value' => $notPunchedToday, 'icon' => 'bi-x-circle', 'color' => 'secondary'])
    </div>
</div>
<div class="card card-panel">
    <div class="card-body">
        <h2 class="h6 fw-semibold mb-3">Employees Still Not Punched</h2>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notPunchedEmployees as $employee)
                        <tr>
                            <td>{{ $employee->employee_code }}</td>
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->department }}</td>
                            <td>{!! status_badge($employee->status) !!}</td>
                            <td><a href="{{ route('admin.employees.history', $employee) }}" class="btn btn-sm btn-outline-primary">View history</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">All active employees have already punched today.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
