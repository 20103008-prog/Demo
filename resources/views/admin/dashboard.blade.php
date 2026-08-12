@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h2 class="h5 mb-0 fw-semibold">Dashboard Overview</h2>
    <p class="small text-muted mb-0">{{ now()->format('D, M j') }}</p>
</div>

{{-- Primary attendance KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-4 col-xl">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-start justify-content-between gap-3">
                <div>
                    <div class="kpi-label">Active Employees</div>
                    <div class="fs-5 fw-bold">{{ $stats['employees'] }}</div>
                </div>
                <a href="{{ route('admin.employees') }}" class="btn btn-outline-primary btn-sm" aria-label="Manage employees">
                    <i class="bi bi-people"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        @include('components.kpi', ['title'=>'Present Today','value'=>$stats['presentToday'],'icon'=>'bi-check2-circle','color'=>'success'])
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-start justify-content-between gap-3">
                <div>
                    <div class="kpi-label">Late Today</div>
                    <div class="fs-5 fw-bold">{{ $stats['lateToday'] }}</div>
                </div>
                <a href="{{ route('admin.late.today') }}" class="btn btn-outline-danger btn-sm" aria-label="View who is late today">
                    <i class="bi bi-clock-history"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl">
        @include('components.kpi', ['title'=>'On Leave Today','value'=>$stats['onLeaveToday'],'icon'=>'bi-calendar-minus','color'=>'warning'])
    </div>
    <div class="col-6 col-md-4 col-xl">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-start justify-content-between gap-3">
                <div>
                    <div class="kpi-label">Punches Today</div>
                    <div class="fs-5 fw-bold">{{ $stats['punchesToday'] }}</div>
                </div>
                <a href="{{ route('admin.today.punches') }}" class="btn btn-outline-primary btn-sm" aria-label="View today's punches">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Secondary metrics + not-punched list --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="row g-3">
            <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Pending Leaves','value'=>$stats['pendingLeaves'],'icon'=>'bi-calendar','color'=>'warning'])</div>
            <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Open Queries','value'=>$stats['openQueries'],'icon'=>'bi-chat','color'=>'info'])</div>
            <div class="col-6 col-md-3">@include('components.kpi', ['title'=>now()->format('M').' Payroll','value'=>inr($stats['payroll']),'icon'=>'bi-cash','color'=>'success'])</div>
            <div class="col-6 col-md-3">@include('components.kpi', ['title'=>'Active Loans','value'=>$stats['activeLoans'],'icon'=>'bi-credit-card','color'=>'danger'])</div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card kpi-card h-100">
            <div class="card-body d-flex align-items-start justify-content-between gap-3 pb-2">
                <div>
                    <div class="kpi-label">Still Not Punched</div>
                    <div class="fs-5 fw-bold">{{ $stats['notPunchedToday'] }}</div>
                </div>
                <a href="{{ route('admin.today.summary') }}" class="btn btn-outline-primary btn-sm">Today</a>
            </div>
            <div class="list-group list-group-flush border-top">
                @forelse($notPunchedEmployees->take(5) as $employee)
                    <a href="{{ route('admin.employees.history', $employee) }}" class="list-group-item list-group-item-action py-2 px-3">
                        <div class="fw-semibold">{{ $employee->name }}</div>
                        <div class="text-muted small">{{ $employee->department }}</div>
                    </a>
                @empty
                    <div class="px-3 py-3 text-muted small">All active employees have punched today.</div>
                @endforelse
            </div>
            @if($notPunchedEmployees->count() > 5)
                <a href="{{ route('admin.today.not.punched') }}" class="list-group-item list-group-item-action text-center small fw-semibold py-2 border-top">
                    View all {{ $notPunchedEmployees->count() }}
                </a>
            @endif
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card card-panel">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Monthly Payroll Trend</h2>
                <div class="chart-box">
                    <canvas data-chart='@json($payrollChart)'></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card card-panel h-100">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Quick Actions</h2>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.employees') }}" class="btn btn-outline-primary btn-sm">Manage Employees</a>
                    <a href="{{ route('admin.payroll') }}" class="btn btn-outline-primary btn-sm">Process Payroll</a>
                    <a href="{{ route('admin.queries') }}" class="btn btn-outline-primary btn-sm">AI Queries</a>
                    <a href="{{ route('admin.audit') }}" class="btn btn-outline-secondary btn-sm">Audit Logs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-lg-7">
        <div class="card card-panel h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-1">Recent Punches</h2>
                        <p class="small text-muted mb-0">Latest punches for today with status and time ago.</p>
                    </div>
                    <a href="{{ route('admin.today.punches') }}" class="btn btn-outline-primary btn-sm">View all</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPunches as $record)
                                @php
                                    $punchTime = $record->check_out ?: $record->check_in;
                                    $punchLabel = $record->check_out ? 'Out' : 'In';
                                    $timestamp = $punchTime ? \Carbon\Carbon::parse($record->date->format('Y-m-d').' '.$punchTime) : null;
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $record->user->name }}</td>
                                    <td>{{ $record->date->format('d M') }}</td>
                                    <td>
                                        {{ $punchTime ?: '—' }}
                                        @if($timestamp)
                                            <div class="text-muted small">{{ $timestamp->diffForHumans() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $record->check_out ? 'secondary' : 'success' }}">
                                            {{ $punchLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No recent punches available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-panel h-100">
            <div class="card-body">
                <h2 class="h6 fw-semibold mb-3">Punch volume · last 7 days</h2>
                <div class="chart-box chart-box-sm">
                    <canvas id="punchVolumeChartCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const config = @json($punchVolumeChart);
        const todayLabel = config?.options?.scales?.x?.ticks?.todayLabel;

        if (typeof todayLabel === 'string' && config?.options?.scales?.x?.ticks) {
            config.options.scales.x.ticks.color = function(context) {
                const index = typeof context.index === 'number' ? context.index : context?.tick?.index;
                let label = '';

                if (typeof index === 'number' && Array.isArray(config.data.labels)) {
                    label = String(config.data.labels[index] ?? '');
                } else {
                    label = String(context?.tick?.label ?? context?.label ?? context?.value ?? '');
                }

                return label === todayLabel ? '#2563eb' : '#6b7280';
            };
        }

        const renderChart = () => {
            const canvas = document.getElementById('punchVolumeChartCanvas');
            if (!canvas || typeof window.Chart !== 'function') {
                return;
            }
            new window.Chart(canvas, config);
        };

        if (typeof window.Chart === 'function') {
            renderChart();
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js';
        script.onload = renderChart;
        document.head.appendChild(script);
    });
</script>
@endsection
