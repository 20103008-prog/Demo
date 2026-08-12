@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Today Status','value'=>$today?->status ?? 'Not punched','sub'=>$today?->check_in ? 'In '.$today->check_in : 'Punch to start','icon'=>'bi-clock','color'=>'primary'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Attendance %','value'=>$attendancePct.'%','sub'=>'This month','icon'=>'bi-graph-up','color'=>'success'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Latest Net Pay','value'=>$latestPayslip ? inr($latestPayslip->net) : '—','sub'=>$latestPayslip?->month,'icon'=>'bi-cash','color'=>'info'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Loan Outstanding','value'=>inr($loanOutstanding),'icon'=>'bi-credit-card','color'=>'warning'])</div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">@include('components.kpi', ['title'=>'Pending Leaves','value'=>$pendingLeaves,'icon'=>'bi-calendar3','color'=>'warning'])</div>
    <div class="col-md-3">@include('components.kpi', ['title'=>'Open Queries','value'=>$openQueries,'icon'=>'bi-chat-dots','color'=>'info'])</div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Recent Attendance</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <thead class="table-light"><tr><th>Date</th><th>In</th><th>Out</th><th>Hours</th><th>Status</th></tr></thead>
                    <tbody>
                    @foreach($recentAttendance as $r)
                        <tr>
                            <td>{{ $r->date->format('d M Y') }}</td>
                            <td>{{ $r->check_in ?: '—' }}</td>
                            <td>{{ $r->check_out ?: '—' }}</td>
                            <td>{{ $r->hours }}</td>
                            <td>{!! status_badge($r->status) !!}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-panel">
            <div class="card-header bg-white border-0 fw-semibold">Welcome, {{ $user->name }}</div>
            <div class="card-body">
                <p class="small text-muted mb-2">{{ $user->job_title }} · {{ $user->department }}</p>
                <p class="small mb-3">Employee ID: <strong>{{ $user->employee_code }}</strong></p>
                <a href="{{ route('employee.attendance') }}" class="btn btn-primary btn-sm me-2">Go to Attendance</a>
                <a href="{{ route('employee.leave') }}" class="btn btn-outline-primary btn-sm">Apply Leave</a>
            </div>
        </div>
    </div>
</div>
@endsection
